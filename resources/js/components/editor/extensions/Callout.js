import { mergeAttributes, Node } from "@tiptap/core";

export const Callout = Node.create({
	name: "callout",

	group: "block",

	content: "inline*",

	addAttributes() {
		return {
			type: {
				default: "info",
			},
		};
	},

	parseHTML() {
		return [
			{
				tag: 'div[data-type="callout"]',
			},
		];
	},

	renderHTML({ HTMLAttributes }) {
		return [
			"div",
			mergeAttributes(HTMLAttributes, {
				"data-type": "callout",
				class: "callout-block",
			}),
			0,
		];
	},

	addCommands() {
		return {
			setCallout:
				(attributes) =>
				({ commands }) => {
					return commands.setNode(this.name, attributes);
				},
			toggleCallout:
				(attributes) =>
				({ commands }) => {
					return commands.toggleNode(this.name, "paragraph", attributes);
				},
		};
	},
});
