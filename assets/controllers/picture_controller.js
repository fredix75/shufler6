import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {



    connect() {
        const bandeau = document.getElementsByClassName('bandeau');
        let bandeauHeight = 0;
        if (bandeau.length !== 0) {
            bandeauHeight = bandeau[0].clientHeight;
        }

        document.addEventListener("scroll", (event) => {
            navbarScroll(bandeauHeight);
        });
        changeColor();

        const myOffcanvas = document.getElementById('offcanvasScrolling')
        const content = document.getElementsByClassName('content')[0];
        const btnIcon = document.getElementById('btn-offcanvas').querySelector('i');
        myOffcanvas.addEventListener('hidden.bs.offcanvas', event => {
            if (document.documentElement.clientWidth > 730) {
                content.style.paddingLeft = '0.75rem';
            }
            btnIcon.classList.remove('bi-box-arrow-in-left');
            btnIcon.classList.add('bi-box-arrow-in-right');
        })
        myOffcanvas.addEventListener('show.bs.offcanvas', event => {
            if (document.documentElement.clientWidth > 730) {
                content.style.paddingLeft = '410px';
            }
            btnIcon.classList.remove('bi-box-arrow-in-right');
            btnIcon.classList.add('bi-box-arrow-in-left');
        })


        function navbarScroll(bandeauHeight = 0) {
            let y = window.scrollY;
            let gallery = document.getElementsByClassName('gallery')[0];

            if (y > 10) {
                document.getElementsByClassName('header')[0].classList.add('small');
                document.getElementsByClassName('offcanvas')[0].classList.add('small');
            } else if (y <= 10) {
                document.getElementsByClassName('header')[0].classList.remove('small');
                document.getElementsByClassName('offcanvas')[0].classList.remove('small');
            }

            if (bandeauHeight !== 0 && y > bandeauHeight) {
                document.getElementsByClassName('bandeau')[0].classList.add('small');
                gallery.style.marginTop = bandeauHeight + 'px';
            } else if (bandeauHeight !== 0 && y <= bandeauHeight) {
                document.getElementsByClassName('bandeau')[0].classList.remove('small');
                gallery.style.marginTop = '0px';
            }
        }

        function changeColor() {
            const x = document.getElementsByClassName('letter-x')[0];
            let r = 255;
            let r_asc = true;
            let g = 150;
            let g_asc = true;
            let b = 0;
            let b_asc = true;

            setInterval(function () {
                x.style.color = `rgb(${r}, ${g}, ${b})`;
                if (r >= 255) {
                    r_asc = false;
                }
                if (r <= 100) {
                    r_asc = true;
                }

                if (g >= 200) {
                    g_asc = false;
                }
                if (g <= 50) {
                    g_asc = true;
                }

                if (b >= 150) {
                    b_asc = false;
                }
                if (b <= 0) {
                    b_asc = true;
                }
                r += r_asc ? 1 : -1;
                g += g_asc ? 3 : -3;
                b += b_asc ? 7 : -7;
            }, 100);
        }
    }

    displayPicture(event) {
        let src = event.currentTarget.dataset.src;
        let img_bg = document.querySelector('#img-bg');
        img_bg.querySelector('span').style.backgroundImage = `url("${src}")`;
        img_bg.style.display = "block";
        event.preventDefault();
    }

    hideBg(event) {
        event.currentTarget.style.display = 'none';
        event.preventDefault();
    }
}
