<?php
/*
 * Proteção do PDF gerado pelo FPDF (padrão de segurança do PDF, RC4 40 bits, revisão 2).
 * Baseado no script "FPDF_Protection" (Klemen Vodopivec), adaptado para FPDF 1.81.
 *
 * Uso: $pdf->DefinirProtecao(array('print'), '', 'senha-do-dono');
 *   - permissões possíveis: print, modify, copy, annot-forms (as que não forem listadas ficam bloqueadas);
 *   - senha de usuário vazia = abre sem senha; a senha do dono libera tudo (gerada aleatória e descartada).
 * Leitores de PDF respeitam as permissões, mas elas não são criptografia forte: servem para
 * impedir cópia/edição no uso normal, não contra quem usa ferramentas de quebra.
 */
require_once __DIR__ . '/fpdf.php';

class FPDF_Protecao extends FPDF
{
    protected $criptografado = false;
    protected $chaveCriptografia = '';
    protected $valorO = '';
    protected $valorU = '';
    protected $valorP = 0;
    protected $objetoCriptografia = 0;
    protected $preenchimento = "\x28\xBF\x4E\x5E\x4E\x75\x8A\x41\x64\x00\x4E\x56\xFF\xFA\x01\x08\x2E\x2E\x00\xB6\xD0\x68\x3E\x80\x2F\x0C\xA9\xFE\x64\x53\x69\x7A";

    public function DefinirProtecao(array $permissoes = array(), $senhaUsuario = '', $senhaDono = null)
    {
        $opcoes = array('print' => 4, 'modify' => 8, 'copy' => 16, 'annot-forms' => 32);
        $protecao = 192;
        foreach ($permissoes as $permissao) {
            if (!isset($opcoes[$permissao])) {
                $this->Error('Permissão de PDF inválida: ' . $permissao);
            }
            $protecao += $opcoes[$permissao];
        }
        if ($senhaDono === null || $senhaDono === '') {
            $senhaDono = bin2hex(random_bytes(16));
        }
        $this->criptografado = true;
        $this->valorP = -(($protecao ^ 255) + 1);
        $this->gerarChave($senhaUsuario, $senhaDono, $protecao);
    }

    protected function gerarChave($senhaUsuario, $senhaDono, $protecao)
    {
        $usuario = substr($senhaUsuario . $this->preenchimento, 0, 32);
        $dono = substr($senhaDono . $this->preenchimento, 0, 32);
        // Valor O: senha do usuário cifrada com a chave derivada da senha do dono.
        $chaveDono = substr(md5($dono, true), 0, 5);
        $this->valorO = $this->rc4($chaveDono, $usuario);
        // Chave do documento (sem /ID, que fica vazio no trailer).
        $this->chaveCriptografia = substr(md5($usuario . $this->valorO . chr($protecao) . "\xFF\xFF\xFF", true), 0, 5);
        // Valor U: preenchimento padrão cifrado com a chave do documento.
        $this->valorU = $this->rc4($this->chaveCriptografia, $this->preenchimento);
    }

    protected function chaveObjeto($n)
    {
        return substr(md5($this->chaveCriptografia . substr(pack('V', $n), 0, 3) . "\x00\x00", true), 0, 10);
    }

    protected function rc4($chave, $texto)
    {
        $s = range(0, 255);
        $j = 0;
        $tamanhoChave = strlen($chave);
        for ($i = 0; $i < 256; $i++) {
            $j = ($j + $s[$i] + ord($chave[$i % $tamanhoChave])) % 256;
            $t = $s[$i];
            $s[$i] = $s[$j];
            $s[$j] = $t;
        }
        $saida = '';
        $a = 0;
        $b = 0;
        $tamanho = strlen($texto);
        for ($i = 0; $i < $tamanho; $i++) {
            $a = ($a + 1) % 256;
            $b = ($b + $s[$a]) % 256;
            $t = $s[$a];
            $s[$a] = $s[$b];
            $s[$b] = $t;
            $saida .= chr(ord($texto[$i]) ^ $s[($s[$a] + $s[$b]) % 256]);
        }
        return $saida;
    }

    protected function _textstring($s)
    {
        if (!$this->_isascii($s)) {
            $s = $this->_UTF8toUTF16($s);
        }
        if ($this->criptografado) {
            $s = $this->rc4($this->chaveObjeto($this->n), $s);
        }
        return '(' . $this->_escape($s) . ')';
    }

    protected function _putstream($data)
    {
        if ($this->criptografado) {
            $data = $this->rc4($this->chaveObjeto($this->n), $data);
        }
        parent::_putstream($data);
    }

    protected function _putresources()
    {
        parent::_putresources();
        if ($this->criptografado) {
            $this->_newobj();
            $this->objetoCriptografia = $this->n;
            $this->_put('<<');
            $this->_put('/Filter /Standard');
            $this->_put('/V 1');
            $this->_put('/R 2');
            $this->_put('/O (' . $this->_escape($this->valorO) . ')');
            $this->_put('/U (' . $this->_escape($this->valorU) . ')');
            $this->_put('/P ' . $this->valorP);
            $this->_put('>>');
            $this->_put('endobj');
        }
    }

    protected function _puttrailer()
    {
        parent::_puttrailer();
        if ($this->criptografado) {
            $this->_put('/Encrypt ' . $this->objetoCriptografia . ' 0 R');
            $this->_put('/ID [()()]');
        }
    }
}
