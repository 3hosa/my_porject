// Simple PJAX implementation: intercept internal links, fetch page, replace .site-container
(function(){
    function isInternalLink(link){
        return location.host === link.host && link.pathname.indexOf('/php1') !== -1;
    }

    function fetchAndReplace(url, push){
        var container = document.querySelector('.site-container');
        if(!container) return;

        // show a lightweight loading indicator
        var loader = document.createElement('div');
        loader.className = 'pjax-loader';
        loader.style.position = 'fixed';
        loader.style.left = '50%';
        loader.style.top = '10px';
        loader.style.transform = 'translateX(-50%)';
        loader.style.background = 'rgba(0,0,0,0.6)';
        loader.style.color = 'white';
        loader.style.padding = '6px 12px';
        loader.style.borderRadius = '6px';
        loader.style.zIndex = 9999;
        loader.textContent = 'جارٍ التحميل...';
        document.body.appendChild(loader);

        fetch(url, {credentials: 'same-origin'})
            .then(function(res){
                if(res.ok) return res.text();
                throw new Error('Network response was not ok');
            })
            .then(function(html){
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var newContainer = doc.querySelector('.site-container');
                if(newContainer){
                    // replace content
                    container.innerHTML = newContainer.innerHTML;
                    // update title
                    var newTitle = doc.querySelector('title');
                    if(newTitle) document.title = newTitle.textContent;

                    // re-run site init
                    if(window.siteInit) window.siteInit();

                    // scroll to top
                    window.scrollTo({top:0, behavior:'smooth'});

                    if(push !== false) history.pushState({pjax: true, url: url}, '', url);
                }
            })
            .catch(function(err){
                console.error('PJAX error', err);
                window.location.href = url; // fallback to normal navigation
            })
            .finally(function(){
                loader.remove();
            });
    }

    document.addEventListener('click', function(e){
        var a = e.target.closest('a');
        if(!a) return;
        if(a.target && a.target !== '' ) return; // respect target
        if(a.hasAttribute('data-no-pjax')) return; // opt-out
        // only intercept same-origin links
        try{
            if(isInternalLink(a)){
                e.preventDefault();
                fetchAndReplace(a.href, true);
            }
        }catch(err){/* ignore */}
    });

    // handle back/forward
    window.addEventListener('popstate', function(e){
        if(e.state && e.state.pjax && e.state.url){
            fetchAndReplace(e.state.url, false);
        } else {
            // reload full page for unknown states
            location.reload();
        }
    });
})();
