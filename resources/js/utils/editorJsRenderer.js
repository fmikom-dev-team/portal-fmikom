/**
 * Converts Editor.js JSON output blocks into an HTML string.
 * Used for preview modal and public post rendering.
 */
export function editorJsToHtml(data) {
	if (!data?.blocks || !Array.isArray(data.blocks)) return "";

	return data.blocks
		.map((block) => blockToHtml(block))
		.filter(Boolean)
		.join("\n");
}

function blockToHtml(block) {
	const { type, data } = block;

	switch (type) {
		case "paragraph":
			return `<p>${data.text || ""}</p>`;

		case "header": {
			const level = Math.min(Math.max(data.level || 2, 1), 6);
			return `<h${level}>${data.text || ""}</h${level}>`;
		}

		case "quote":
			return `<blockquote>
                <p>${data.text || ""}</p>
                ${data.caption ? `<cite>${data.caption}</cite>` : ""}
            </blockquote>`;

		case "list": {
			const listTag = data.style === "ordered" ? "ol" : "ul";
			const items = (data.items || [])
				.map((item) => {
					// Handle both string items and object items (nested list)
					const text = typeof item === "string" ? item : item.content || "";
					return `<li>${text}</li>`;
				})
				.join("");
			return `<${listTag}>${items}</${listTag}>`;
		}

		case "checklist": {
			const checks = (data.items || [])
				.map(
					(item) =>
						`<li class="checklist-item ${item.checked ? "checked" : ""}">
                    <span class="checklist-box">${item.checked ? "✓" : ""}</span>
                    <span>${item.text || ""}</span>
                </li>`,
				)
				.join("");
			return `<ul class="checklist">${checks}</ul>`;
		}

		case "code": {
			const lang = data.language || "";
			return `<pre><code class="language-${lang}">${escapeHtml(data.code || "")}</code></pre>`;
		}

		case "delimiter":
			return `<hr class="editorjs-delimiter" />`;

		case "image": {
			const imgClass = [
				data.stretched ? "img-stretched" : "",
				data.withBorder ? "img-border" : "",
				data.withBackground ? "img-background" : "",
			]
				.filter(Boolean)
				.join(" ");
			return `<figure class="editorjs-image ${imgClass}">
                <img src="${data.file?.url || data.url || ""}" alt="${data.caption || ""}" loading="lazy" />
                ${data.caption ? `<figcaption>${data.caption}</figcaption>` : ""}
            </figure>`;
		}

		case "embed":
			if (data.service === "youtube" || data.embed?.includes("youtube")) {
				return `<div class="editorjs-embed">
                    <iframe src="${data.embed}" width="${data.width || "100%"}" height="${data.height || 480}" 
                        frameborder="0" allowfullscreen loading="lazy"></iframe>
                    ${data.caption ? `<p class="embed-caption">${data.caption}</p>` : ""}
                </div>`;
			}
			return `<div class="editorjs-embed">
                <iframe src="${data.embed}" frameborder="0" allowfullscreen loading="lazy"></iframe>
                ${data.caption ? `<p class="embed-caption">${data.caption}</p>` : ""}
            </div>`;

		case "table": {
			const rows = (data.content || [])
				.map((row, rowIdx) => {
					const cells = row
						.map((cell) =>
							rowIdx === 0 && data.withHeadings
								? `<th>${cell}</th>`
								: `<td>${cell}</td>`,
						)
						.join("");
					return `<tr>${cells}</tr>`;
				})
				.join("");
			return `<div class="editorjs-table-wrapper"><table><tbody>${rows}</tbody></table></div>`;
		}

		case "attaches":
			return `<a href="${data.file?.url || ""}" class="editorjs-attachment" download target="_blank" rel="noopener">
                <span class="attach-icon">📎</span>
                <span class="attach-name">${data.title || data.file?.name || "File"}</span>
                <span class="attach-size">${formatFileSize(data.file?.size)}</span>
            </a>`;

		case "warning":
			return `<div class="editorjs-warning">
                <strong>${data.title || "Perhatian"}</strong>
                <p>${data.message || ""}</p>
            </div>`;

		case "alert":
			return `<div class="editorjs-alert editorjs-alert--${data.type || "info"}">
                <p>${data.message || ""}</p>
            </div>`;

		case "raw":
			return data.html || "";

		default:
			// Fallback: try to render text if it exists
			if (data.text) return `<p>${data.text}</p>`;
			return "";
	}
}

function escapeHtml(str) {
	return str
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;")
		.replace(/"/g, "&quot;")
		.replace(/'/g, "&#39;");
}

function formatFileSize(bytes) {
	if (!bytes) return "";
	if (bytes < 1024) return `${bytes} B`;
	if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
	return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

/**
 * Count words from Editor.js blocks
 */
export function countWordsFromEditorJs(data) {
	if (!data?.blocks) return 0;
	const textBlocks = data.blocks
		.map((b) => b.data?.text || b.data?.code || "")
		.join(" ");
	return textBlocks.trim().split(/\s+/).filter(Boolean).length;
}
