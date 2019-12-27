"use strict";
var ExtendedGitGraph2;
(function (ExtendedGitGraph2) {
    function formatDate(date) {
        /*
        const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        const days       = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        let wday = days[date.getDay()];
        let day = date.getDate();
        let monthIndex = date.getMonth();
        let year = date.getFullYear();

        let suffix = 'th';
        if (day === 1) suffix = 'st';
        if (day === 2) suffix = 'nd';
        if (day === 3) suffix = 'rd';

        return wday + ' ' + day + suffix + ' ' + monthNames[monthIndex] + ', ' + year;
        */
        return date.getFullYear() + "-" + date.getMonth().toString().padStart(2, '0') + "-" + date.getDay().toString().padStart(2, '0');
    }
    function initHover() {
        const allsvgtips = Array.from(document.getElementsByClassName("svg-tip"));
        if (allsvgtips.length == 0)
            return;
        const masterTip = allsvgtips[0];
        masterTip.style.opacity = '1';
        masterTip.style.display = 'none';
        const masterTipHeader = masterTip.getElementsByTagName('strong')[0];
        const masterTipContent = masterTip.getElementsByTagName('span')[0];
        const rects = Array.from(document.getElementsByClassName("egg_rect"));
        for (let rect of rects) {
            rect.addEventListener("mouseenter", event => {
                const target = event.target;
                let datesplit = target.getAttribute('data-date').split('-');
                let count = target.getAttribute('data-count');
                let date = new Date(Number(datesplit[0]), Number(datesplit[1]) - 1, Number(datesplit[2]));
                masterTip.style.display = 'block';
                masterTipHeader.innerHTML = count + ' commits';
                masterTipContent.innerHTML = ' on ' + formatDate(date);
                masterTip.style.left = (window.pageXOffset + target.getBoundingClientRect().left - masterTip.getBoundingClientRect().width / 2 - 3.5 + 9) + 'px';
                masterTip.style.top = (window.pageYOffset + target.getBoundingClientRect().top - masterTip.getBoundingClientRect().height - 10) + 'px';
            });
            rect.addEventListener("mouseleave", _ => masterTip.style.display = 'none');
        }
    }
    ExtendedGitGraph2.initHover = initHover;
})(ExtendedGitGraph2 || (ExtendedGitGraph2 = {}));
window.onload = () => { ExtendedGitGraph2.initHover(); };
