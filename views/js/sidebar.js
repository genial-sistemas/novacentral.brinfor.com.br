document.addEventListener('DOMContentLoaded', function () {
    var links = document.querySelectorAll('.az-sidebar .nav-link.with-sub');

    links.forEach(function (link) {
        link.setAttribute('aria-expanded', String(link.parentElement.classList.contains('show')));
    });

    document.addEventListener('click', function (event) {
        var link = event.target.closest('.az-sidebar .nav-link.with-sub');
        if (!link) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        var item = link.parentElement;
        var abrir = !item.classList.contains('show');

        Array.prototype.forEach.call(item.parentElement.children, function (irmao) {
            if (irmao !== item && irmao.classList.contains('nav-item')) {
                irmao.classList.remove('show');
                var linkIrmao = irmao.querySelector('.nav-link.with-sub');
                if (linkIrmao) {
                    linkIrmao.setAttribute('aria-expanded', 'false');
                }
            }
        });

        item.classList.toggle('show', abrir);
        link.setAttribute('aria-expanded', String(abrir));
    }, true);
});
