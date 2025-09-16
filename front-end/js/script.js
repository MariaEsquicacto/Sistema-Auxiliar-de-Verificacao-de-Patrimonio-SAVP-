 // Funções do Menu Hambúrguer
 document.addEventListener('DOMContentLoaded', () => {
    const abrir_menu = document.querySelector('.hamburguer');
    const menu = document.querySelector('.menu');

    if (abrir_menu && menu) {
        abrir_menu.addEventListener('click', () => {
            abrir_menu.classList.toggle('aberto');
            menu.classList.toggle('ativo');
        });
    }
});
