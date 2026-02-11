<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISBN Barcode Generator - High Res</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&family=JetBrains+Mono:wght@400;700&family=PT+Mono:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <link rel="stylesheet" href="index.css?v=1.1" />
</head>
<body>

<nav class="nav">
    <div style="display: flex; gap: 20px;">
        <a href="files.php">File name</a>
    </div>
</nav>

<div class="card">
    <div class="inner-content">
        <textarea id="isbnList" placeholder="Enter ISBN-13 codes here (Format: ISBN, Name)" oninput="updateUI()"></textarea>
        <div id="statsBar" class="stats-bar"><span id="rowCount">0 Rows Detected</span></div>
        <div class="btn-group">
            <button class="btn-primary" onclick="generateBatch()">Create Barcodes</button>
            <button id="downloadAllBtn" class="btn-zip" style="display: none;" onclick="downloadAllAsZip()">Export To Zip</button>
        </div>
    </div>
</div>

<div id="barcodeContainer"></div>

<script>
    const L_CODES = { 0: "0001101", 1: "0011001", 2: "0010011", 3: "0111101", 4: "0100011", 5: "0110001", 6: "0101111", 7: "0111011", 8: "0110111", 9: "0001011" };
    const G_CODES = { 0: "0100111", 1: "0110011", 2: "0011011", 3: "0100001", 4: "0011101", 5: "0111001", 6: "0000101", 7: "0010001", 8: "0001001", 9: "0010111" };
    const R_CODES = { 0: "1110010", 1: "1100110", 2: "1101100", 3: "1000010", 4: "1011100", 5: "1001110", 6: "1010000", 7: "1000100", 8: "1001000", 9: "1110100" };
    const PARITY = { 0: "LLLLLL", 1: "LLGLGG", 2: "LLGGLG", 3: "LLGGGL", 4: "LGLLGG", 5: "LGGLLG", 6: "LGGGLL", 7: "LGLGLG", 8: "LGLGGL", 9: "LGGLGL" };

    const SCALE = 4; 

    function validISBN13(code) {
        if (!/^\d{13}$/.test(code)) return false;
        let sum = 0;
        for (let i = 0; i < 12; i++) sum += parseInt(code[i]) * (i % 2 === 0 ? 1 : 3);
        return (10 - (sum % 10)) % 10 === parseInt(code[12]);
    }

    function encodeEAN13(code) {
        const first = parseInt(code[0]);
        const left = code.slice(1, 7);
        const right = code.slice(7);
        let result = "101";
        const pattern = PARITY[first];
        for (let i = 0; i < left.length; i++) {
            result += pattern[i] === "L" ? L_CODES[left[i]] : G_CODES[left[i]];
        }
        result += "01010";
        for (let d of right) result += R_CODES[d];
        return result + "101";
    }

    function updateUI() {
        const val = document.getElementById("isbnList").value.trim();
        const lines = val ? val.split('\n').filter(l => l.trim()) : [];
        document.getElementById("rowCount").innerText = `${lines.length} Rows Detected`;
    }

    function generateBatch() {
        const container = document.getElementById("barcodeContainer");
        const downloadAllBtn = document.getElementById("downloadAllBtn");
        const rawData = document.getElementById("isbnList").value;
        const lines = rawData.split(/\r?\n/).filter(l => l.trim().length > 0);
        container.innerHTML = "";

        lines.forEach((line) => {
            const parts = line.split(/\t|,| {2,}/);
            const digits = parts[0].replace(/[^0-9]/g, ""); 
            const presetName = (parts[1] && parts[1].trim()) ? parts[1].trim() : digits;

            if (!validISBN13(digits)) return;

            const itemDiv = document.createElement("div");
            itemDiv.className = "barcode-item";

            const canvas = document.createElement("canvas");
            canvas.width = 380 * SCALE;
            canvas.height = 180 * SCALE;
            canvas.style.width = "380px";
            canvas.style.height = "180px";
            canvas.dataset.isbn = digits;

            const nameInput = document.createElement("input");
            nameInput.className = "file-name-input";
            nameInput.value = presetName;

            const dlBtn = document.createElement("button");
            dlBtn.className = "download-btn";
            dlBtn.innerText = "Download PNG";
            dlBtn.onclick = () => {
                const finalName = nameInput.value.trim().replace(/[/\\?%*:|"<>]/g, "") || digits;
                const link = document.createElement("a");
                link.download = `${finalName}.png`;
                link.href = canvas.toDataURL("image/png");
                link.click();
            };

            itemDiv.appendChild(canvas);
            itemDiv.appendChild(nameInput);
            itemDiv.appendChild(dlBtn);
            container.appendChild(itemDiv);

            drawBarcode(canvas, encodeEAN13(digits), digits);
        });

        downloadAllBtn.style.display = container.children.length ? "inline-block" : "none";
    }

    async function downloadAllAsZip() {
        const zip = new JSZip();
        const items = document.querySelectorAll(".barcode-item");
        if (!items.length) return;

        items.forEach(item => {
            const canvas = item.querySelector("canvas");
            const input = item.querySelector("input");
            const fileName = input.value.trim().replace(/[/\\?%*:|"<>]/g, "") || canvas.dataset.isbn;
            const imageData = canvas.toDataURL("image/png").split("base64,")[1];
            zip.file(`${fileName}.png`, imageData, { base64: true });
        });

        const content = await zip.generateAsync({ type: "blob" });
        const link = document.createElement("a");
        link.href = URL.createObjectURL(content);
        link.download = "barcodes_high_res.zip";
        link.click();
    }

    function drawBarcode(canvas, bits, digits) {
        const ctx = canvas.getContext("2d");
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        function roundRect(ctx, x, y, w, h, r) {
            ctx.beginPath();
            ctx.moveTo(x + r, y);
            ctx.lineTo(x + w - r, y);
            ctx.quadraticCurveTo(x + w, y, x + w, y + r);
            ctx.lineTo(x + w, y + h - r);
            ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
            ctx.lineTo(x + r, y + h);
            ctx.quadraticCurveTo(x, y + h, x, y + h - r);
            ctx.lineTo(x, y + r);
            ctx.quadraticCurveTo(x, y, x + r, y);
            ctx.closePath();
        }

        const padding = 50 * SCALE;
        const barWidth = ((380 * SCALE) - padding * 2) / 95;
        const longH = 130 * SCALE;
        const shortH = 108 * SCALE; 
        const yT = 15 * SCALE;

        const rectLeftPad = 32 * SCALE; 
        const rectRightPad = 32 * SCALE; 
        const rectX = padding - rectLeftPad;
        const rectW = ((380 * SCALE) - padding * 2) + rectLeftPad + rectRightPad;
        const rectH = longH + (36 * SCALE); 
        const radius = 10 * SCALE;

        ctx.fillStyle = "#ffffff";
        roundRect(ctx, rectX, yT - (5 * SCALE), rectW, rectH, radius);
        ctx.fill();

        ctx.fillStyle = "#000000";
        for (let i = 0; i < bits.length; i++) {
            if (bits[i] === "1") {
                const isGuard = i < 3 || (i > 44 && i < 50) || i > 91;
                ctx.fillRect(
                    padding + i * barWidth, 
                    yT, 
                    Math.ceil(barWidth), 
                    isGuard ? longH : shortH
                );
            }
        }

        const textSize = 26 * SCALE;
        ctx.font = `${textSize}px 'PT Mono', monospace`;
        ctx.textAlign = "center";
        ctx.textBaseline = "top";

        const textNudge = 2 * SCALE; 
        const textY = yT + shortH + textNudge;

        const leadingX = padding - (18 * SCALE);
        ctx.fillText(digits[0], leadingX, textY);

        const leftGroup = digits.slice(1, 7);
        const leftCenterX = padding + 24 * barWidth;
        ctx.fillText(leftGroup, leftCenterX, textY);

        const rightGroup = digits.slice(7);
        const rightCenterX = padding + 71 * barWidth;
        ctx.fillText(rightGroup, rightCenterX, textY);
    }
</script>
</body>
</html>