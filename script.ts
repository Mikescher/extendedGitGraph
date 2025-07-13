namespace ExtendedGitGraph2
{
    export function initHover()
    {
        const allsvgtips = Array.from(document.getElementsByClassName("svg-tip"));
        if (allsvgtips.length == 0) return;

        const masterTip = allsvgtips[0] as HTMLElement;

        masterTip.style.opacity = '1';
        masterTip.style.display = 'none';

        const masterTipHeader  = masterTip.getElementsByTagName('strong')[0];
        const masterTipContent = masterTip.getElementsByTagName('span')[0];
        const masterTipExtra = masterTip.getElementsByTagName('div')[0];

        const rects = Array.from(document.getElementsByClassName("egg_rect")) as HTMLElement[];

        for (let rect of rects)
        {
            rect.addEventListener("mouseenter", event =>
            {
                const target = event.target as HTMLElement;

                let count = target.getAttribute('data-count');
                let date  = target.getAttribute('data-date');

                masterTip.style.display = 'block';

                masterTipHeader.innerHTML = count + ' commits';
                masterTipContent.innerHTML = ' on ' + date;

                if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {

                    masterTipExtra.style.display = 'grid';

                    const extraAttr = target.getAttributeNames().sort().filter(p => p.startsWith('data-repo-')).map(p => target.getAttribute(p)!);

                    let extraHTML = '';
                    for (const attr of extraAttr) {
                        try {
                            let obj = JSON.parse(attr.replace(/'/g, '"'));
                            extraHTML += `<span class="extra-attr">${obj.repo_name}:</span><strong>${obj.count}</strong>\n`;
                        } catch (e) {
                            console.error('Error parsing extra attribute:', attr, e);
                        }
                    }

                    masterTipExtra.innerHTML = extraHTML;

                } else {

                    masterTipExtra.style.display = 'none';

                }

                masterTip.style.left = (window.pageXOffset + target.getBoundingClientRect().left - masterTip.getBoundingClientRect().width /2 - 3.5 + 9) + 'px';
                masterTip.style.top  = (window.pageYOffset + target.getBoundingClientRect().top  - masterTip.getBoundingClientRect().height -10)         + 'px';
            });

            //rect.addEventListener("mouseleave", _ => masterTip.style.display = 'none');
        }
    }
}

window.onload = () => { ExtendedGitGraph2.initHover(); };