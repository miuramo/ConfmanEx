function openclose(elemid) {
    var content = document.getElementById(elemid);
    if (content.style.display === 'none') {
        content.style.display = 'block';
        setTimeout(function() {
            content.style.opacity = '1';
        }, 10);
    } else {
        content.style.opacity = '0';
        setTimeout(function() {
            content.style.display = 'none';
        }, 500);
    }
}

