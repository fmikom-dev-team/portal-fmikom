import { mergeAttributes, Node } from "@tiptap/core";

export const DownloadButton = Node.create({
	name: "downloadButton",
	group: "inline",
	inline: true,
	selectable: true,
	draggable: true,

	addAttributes() {
		return {
			href: {
				default: null,
			},
			text: {
				default: "Download File",
			},
		};
	},

	parseHTML() {
		return [
			{
				tag: 'a[class="btn-download"]',
			},
		];
	},

	renderHTML({ HTMLAttributes }) {
		return [
			"a",
			mergeAttributes(HTMLAttributes, {
				class:
					"btn-download inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold hover:bg-blue-700 transition-colors no-underline my-2 cursor-pointer",
			}),
			HTMLAttributes.text,
		];
	},

	addCommands() {
		return {
			setDownloadButton:
				(options) =>
				({ commands }) => {
					return commands.insertContent({
						type: this.name,
						attrs: options,
					});
				},
		};
	},
});
