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
    if (query === undefined) {
        return;
    }
    if (query.length > -1) {
        $.ajax({
            url: searchUrl,
            method: 'GET',
            data: { query: query },
            success: function (data) {
                $('#results').empty();
                // console.log(data);
                data['mt'].forEach((mt, idx) => {
                    let row = (idx % 2 == 1) ? 'bg-pink-50 dark:bg-pink-400' : 'bg-white  dark:bg-pink-300';
                    $('#results').append('<tr class="' + row + '"><td class="px-2 py-1 text-center">' +
                        '<input type="checkbox" name="mt_' + mt.id + '" value="on">' +
                        '</td>' +
                        '<td class="px-2 py-1 text-center">' + mt.id + '</td>' +
                        '<td class="px-2 py-1">' +
                        '<a class="hover:font-bold hover:text-blue-600 block break-all"' +
                        'href="/mt/' + mt.id + '/edit" target="editmt_' + mt.id + '">' +
                        mt.to + '</a></td>' +
                        '<td class="px-2 py-1">' +
                        '<a class="hover:font-bold hover:text-lime-600"' +
                        'href="/mt/' + mt.id + '" target="previewmt_' + mt.id + '">' +
                        mt.subject + '</a>' +
                        '</td>' +
                        '<td class="px-2 py-1">' +
                        mt.name +
                        '</td>' +
                        '<td class="px-2 py-1">' + mt.lastsent + '</td>' +
                        '<td class="px-2 py-1">' + mt.updated_at + '</td>' +
                        '<td class="px-2 py-1">' +

                        '<a href="/mt/' + mt.id + '" target="_self" onclick="return true" class="inline-flex justify-center py-1 px-2 mb-0.5' +
                        ' border border-transparent shadow-sm text-xs font-medium rounded-md' +
                        ' text-lime-700 bg-lime-300 hover:text-lime-50 hover:bg-lime-500' +
                        ' focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500' +
                        ' dark:bg-lime-500 dark:text-lime-200 dark:hover:bg-lime-300 dark:hover:text-lime-700">送信前確認</a>' +

                        ' <a href="/mt/' + mt.id + '/edit" target="_self" onclick="return true" class="inline-flex justify-center py-1 px-2 mb-0.5' +
                        ' border border-transparent shadow-sm text-xs font-medium rounded-md' +
                        ' text-blue-700 bg-blue-300 hover:text-blue-50 hover:bg-blue-500' +
                        ' focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500' +
                        ' dark:bg-blue-500 dark:text-blue-200 dark:hover:bg-blue-300 dark:hover:text-blue-700">' +
                        '    雛形を編集' +
                        '</a>' +
                        '</td>' +
                        '</tr>');
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

