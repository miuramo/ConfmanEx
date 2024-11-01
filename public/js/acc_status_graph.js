

// SVG要素の設定
const width = 1200, height = 900;
const svg = d3.select("main").append("svg")
    .attr("width", width)
    .attr("height", height);
// .call(d3.zoom()
//     .scaleExtent([0.2, 3])  // ズーム範囲（0.2倍から3倍まで）
//     .on("zoom", (event) => {
//         svg.attr("transform", event.transform);
//     }));

// Tooltip要素の作成
const tooltip = d3.select("body").append("div")
    .attr("class", "tooltip");

// シミュレーションの設定
const simulation = d3.forceSimulation(nodes)
    .force("link", d3.forceLink(links).id(d => d.id).distance(110))
    .force("charge", d3.forceManyBody().strength(-220))
    .force("center", d3.forceCenter(width / 2, height / 2))
    // 以下の2行で画面の中央にノードを引き寄せる
    .force("x", d3.forceX(width / 2).strength(0.08))
    .force("y", d3.forceY(height / 2).strength(0.08));

// リンクの描画
const link = svg.append("g")
    .attr("class", "links")
    .selectAll("line")
    .data(links)
    .enter().append("line")
    .attr("class", "link");

// ノードの描画
const node = svg.append("g")
    .attr("class", "nodes")
    .selectAll("g")
    .data(nodes)
    .enter().append("g")
    .call(drag(simulation));

// 四角形ノードの描画
node.append("rect")
    .attr("class", d => "node " + (d.type === "A" ? "typeA" : "typeB"))
    .attr("width", d => d.width)
    .attr("height", 30)
    .attr("x", d => -d.width / 2)
    .attr("y", -15);

// ノード番号の描画
node.append("text")
    .text(d => d.label);

// Tooltipの表示と非表示
node.on("mouseover", (event, d) => {
    tooltip
        .style("opacity", 1)
        .html(`ID: ${d.id}<br>Label: ${d.label}`);
})
    .on("mousemove", (event) => {
        tooltip
            .style("left", (event.pageX + 10) + "px")
            .style("top", (event.pageY - 20) + "px");
    })
    .on("mouseout", () => {
        tooltip.style("opacity", 0);
    });

// ノードに対するドラッグ操作の定義
function drag(simulation) {
    function dragstarted(event, d) {
        if (!event.active) simulation.alphaTarget(0.3).restart();
        d.fx = d.x;
        d.fy = d.y;
    }

    function dragged(event, d) {
        d.fx = event.x;
        d.fy = event.y;
    }

    function dragended(event, d) {
        if (!event.active) simulation.alphaTarget(0);
        d.fx = null;
        d.fy = null;
    }

    return d3.drag()
        .on("start", dragstarted)
        .on("drag", dragged)
        .on("end", dragended);
}

// シミュレーションの更新処理
simulation.on("tick", () => {
    link
        .attr("x1", d => d.source.x)
        .attr("y1", d => d.source.y)
        .attr("x2", d => d.target.x)
        .attr("y2", d => d.target.y);

    node
        .attr("transform", d => `translate(${d.x},${d.y})`);
});

// ドラッグ中のみドラッグしたノードにのみ力を適用
node.on("mousedown", (event, d) => {
    simulation.nodes([d]).alpha(0.3).restart();
});

node.on("mouseup", () => {
    simulation.nodes(nodes).alpha(0);
});
