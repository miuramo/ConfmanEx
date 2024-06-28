@props([
    'id' => null,
    'paper' => [],
])
<!-- components.paper.authorlist -->

<div class="mx-6 my-0">
    <div class="text-lg mt-2 mb-1 p-3 bg-slate-200 rounded-lg dark:bg-slate-800 dark:text-slate-400">
        著者名と所属
        <x-element.gendospan>採択の場合のみ、入力していただきます</x-element.gendospan>
    </div>
    <form action="{{ route('paper.update_authorlist', ['paper' => $paper]) }}" method="post" id="authorlist">
        @csrf
        @method('put')
        <div class="mx-4 mb-1">
            <label for="authors"
                class="block text-sm font-medium text-gray-900 dark:text-gray-400">右のサンプルを参考に、一行に一人ずつ、氏名のあいだに半角スペースをいれてください。所属に(株)などの括弧は含めないでください。
                複数所属のときは/で区切ってください。部署や部局、研究室名を入力する必要はありません。<span class="text-red-800">（シンポジウムの予稿集・出版担当が表記を短くしたり、PDFの記載に合致させたりする場合があります。）</span></label>
            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-2">
                <div class="p-0">
                    <div class="text-sm px-2  dark:text-gray-400">和文著者・所属</div>
                    <textarea id="authors" name="authorlist" rows="4"
                        class="inline-flex mb-1 p-2.5 w-full text-md text-gray-900 bg-gray-50 rounded-lg  border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        placeholder="投稿 太郎 (投稿大学)&#10;和布蕪 二郎 (和布蕪大学)&#10;昆布 巻子 (ダシ大学/昆布研究所)">{{ $paper->authorlist }}</textarea>
                </div>
                <div class="p-0">
                    <div class="text-sm px-2  dark:text-gray-400">和文著者・所属サンプル</div>
                    <textarea id="jpex" name="jpexample" rows="4"
                        class="inline-flex mb-1 block p-2.5 w-full text-md text-gray-900 bg-gray-200 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        placeholder="※和文著者・所属サンプルです。ここには入力できません。&#10;投稿 太郎 (投稿大学)&#10;和布蕪 二郎 (和布蕪大学)&#10;昆布 巻子 (ダシ大学/昆布研究所)" readonly></textarea>
                </div>

                <div class="p-0">
                    <div class="text-sm px-2  dark:text-gray-400">英文著者・所属（姓→名の順で記す場合は、姓のあとに, (カンマ)を入れることが多いです。）</div>
                    <textarea id="eauthors" name="eauthorlist" rows="4"
                        class="inline-flex mb-1 p-2.5 w-full text-md text-gray-900 bg-gray-50 rounded-lg  border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        placeholder="Toukou, Taro (Toukou University)&#10;Mekabu, Jiro (Mekebu University)&#10;Kombu, Makiko (Dashi University/Kombu Laboratory)">{{ $paper->eauthorlist }}</textarea>
                </div>
                <div class="p-0">
                    <div class="text-sm px-2  dark:text-gray-400">英文著者・所属サンプル</div>
                    <textarea id="enex" name="enexample" rows="4"
                        class="inline-flex mb-1 block p-2.5 w-full text-md text-gray-900 bg-gray-200 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        placeholder="※英文著者・所属サンプルです。ここには入力できません。&#10;Toukou, Taro (Toukou University)&#10;Mekabu, Jiro (Mekebu University)&#10;Kombu, Makiko (Dashi University/Kombu Laboratory)"
                        readonly></textarea>
                </div>
            </div>

            <x-element.submitbutton2 color="teal" :value="1">
                著者名と所属を更新
            </x-element.submitbutton2>
        </div>
        <div class="mx-4 mb-1" id="authorlist_confirm"></div>
    </form>
</div>
