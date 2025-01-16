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
                    data['u'].forEach(user => {
                        $('#results').append(`<li><a class="font-bold text-blue-600" href="/add_to_role/${rolename}/${user.id}">[追加]</a> (u${user.id}) ${user.name} ${user.affil} ${user.email} </li>`);
                    });
                }
            });
        } else {
            $('#results').empty();
        }
    }

    // デバウンスを適用したイベントリスナー
    $('#search-box').on('input', debounce(function () {
        const query = $(this).val();
        performSearch(query);
    }, 500)); // 500ms待つ
});
