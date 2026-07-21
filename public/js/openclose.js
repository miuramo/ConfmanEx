function openclose(elemid) {
    var content = document.getElementById(elemid);
    var isHidden = content.style.display === 'none' || content.style.display === '';

    content.style.transition = 'max-height 0.4s ease, opacity 0.4s ease';
    content.style.overflow = 'hidden';

    if (isHidden) {
        content.style.display = 'block';
        content.style.maxHeight = '0';
        content.style.opacity = '0';
        // reflow を強制してトランジションを有効にする
        content.offsetHeight;
        content.style.maxHeight = content.scrollHeight + 'px';
        content.style.opacity = '1';
        content.addEventListener('transitionend', function handler(e) {
            if (e.propertyName !== 'max-height') return;
            content.style.maxHeight = 'none';
            content.style.overflow = '';
            content.removeEventListener('transitionend', handler);
        });
    } else {
        content.style.maxHeight = content.scrollHeight + 'px';
        content.style.opacity = '1';
        // reflow を強制してトランジションを有効にする
        content.offsetHeight;
        content.style.maxHeight = '0';
        content.style.opacity = '0';
        content.addEventListener('transitionend', function handler(e) {
            if (e.propertyName !== 'max-height') return;
            content.style.display = 'none';
            content.style.maxHeight = '';
            content.style.overflow = '';
            content.removeEventListener('transitionend', handler);
        });
    }
}

