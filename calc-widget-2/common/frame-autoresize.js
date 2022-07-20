(function () {
    var selfScript = document.currentScript;

    var id = `${(+new Date).toString(16)}`;

    var frame = document.createElement('iframe');
    frame.style.width = '0';
    frame.style.minWidth = '100%';
    frame.style.minHeight = '500px';
    frame.style.height = '710px';
    frame.style.border = 'none';
    frame.style.backgroundColor = 'transparent';
    frame.setAttribute('autoheight', '');

    frame.onload = function () {
        window.addEventListener('message', function (event) {
            try {
                var message = JSON.parse(event.data);
                if (message.event === 'resize' && message.data.id === id) {
                    frame.style.height = message.data.offsetHeight + 'px';
                    frame.contentWindow.postMessage(JSON.stringify({
                        event: 'autoheight'
                    }), '*');
                }
            } catch (e) { }
        });
    };
    
    selfScript.parentNode.insertBefore(frame, selfScript);
    
    frame.setAttribute('src', 'https://janesv.github.io/wp-content/calc-widget-2/broker-kapital/index-casco.html?id=' + id);
})();