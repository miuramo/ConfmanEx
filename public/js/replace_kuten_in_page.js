// ページがロードされた後に実行
window.onload = function () {
    // ページ内のすべてのテキストノードを再帰的に取得する関数。ただし、table内部 は除く。
    function getTextNodes(node, excludeTags = ['table']) {
        let textNodes = [];
        // ノードが除外対象のタグに含まれていないかチェック
        if (excludeTags.includes(node.nodeName.toLowerCase())) {
            return textNodes;
        }

        if (node.nodeType === Node.TEXT_NODE) {
            textNodes.push(node);
        } else {
            node.childNodes.forEach(child => {
                textNodes = textNodes.concat(getTextNodes(child));
            });
        }
        return textNodes;
    }

    // ページ内のすべてのテキストノードを取得
    let textNodes = getTextNodes(document.body);

    // 各テキストノードの内容を置き換え
    textNodes.forEach(node => {
        node.nodeValue = node.nodeValue.replace(/。/g, '．');
        node.nodeValue = node.nodeValue.replace(/、/g, '，');
    });
};