$(document).ready(function() {
    // デバウンス関数の定義
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Ajaxリクエストを送る処理
    function performSearch(query) {
        if (query.length > 0) {
            $.ajax({
                url: searchUrl,
                method: 'GET',
                data: { query: query },
                success: function (data) {
                    $('#results').empty();
                    // console.log(data);
                    data['u'].forEach(item => {
                        $('#results').append(`<li><a class="font-bold text-green-600" href="/login-as/${item.id}/${data['id']}">(u${item.id})</a> ${item.name} ${item.affil} ${item.email} </li>`);
                    });
                    data['p'].forEach(item => {
                        $('#presults').append(`<li><a class="font-bold text-orange-600" href="/login-as/${item.owner}/${data['id']}">(p${item.id})</a> ${item.title} ${item.authorlist} </li>`);
                    });
                }
            });
        } else {
            $('#results').empty();
            $('#presults').empty();
        }
    }

    // デバウンスを適用したイベントリスナー
    $('#search-box').on('input', debounce(function () {
        const query = $(this).val();
        performSearch(query);
    }, 500)); // 500ms待つ
});
