import { VueRenderer } from "@tiptap/vue-3";
import tippy from "tippy.js";
import CommandList from "./CommandList.vue";

export const COMMANDS = [
	// ── Basic ──
	{
		title: "Text",
		description: "Just start writing",
		icon: "Type",
		group: "Basic",
		command: ({ editor, range }) =>
			editor.chain().focus().deleteRange(range).setNode("paragraph").run(),
	},
	{
		title: "Heading 1",
		description: "Large section heading",
		icon: "Heading1",
		group: "Basic",
		command: ({ editor, range }) =>
			editor
				.chain()
				.focus()
				.deleteRange(range)
				.setNode("heading", { level: 1 })
				.run(),
	},
	{
		title: "Heading 2",
		description: "Medium section heading",
		icon: "Heading2",
		group: "Basic",
		command: ({ editor, range }) =>
			editor
				.chain()
				.focus()
				.deleteRange(range)
				.setNode("heading", { level: 2 })
				.run(),
	},
	{
		title: "Heading 3",
		description: "Small section heading",
		icon: "Heading3",
		group: "Basic",
		command: ({ editor, range }) =>
			editor
				.chain()
				.focus()
				.deleteRange(range)
				.setNode("heading", { level: 3 })
				.run(),
	},
	{
		title: "Bullet List",
		description: "Unordered list",
		icon: "List",
		group: "Basic",
		command: ({ editor, range }) =>
			editor.chain().focus().deleteRange(range).toggleBulletList().run(),
	},
	{
		title: "Numbered List",
		description: "Ordered list",
		icon: "ListOrdered",
		group: "Basic",
		command: ({ editor, range }) =>
			editor.chain().focus().deleteRange(range).toggleOrderedList().run(),
	},
	{
		title: "To-do List",
		description: "Track tasks",
		icon: "CheckSquare",
		group: "Basic",
		command: ({ editor, range }) =>
			editor.chain().focus().deleteRange(range).toggleTaskList().run(),
	},

	// ── Media ──
	{
		title: "Image",
		description: "Upload or embed an image",
		icon: "Image",
		group: "Media",
		command: ({ editor, range }) => {
			editor.chain().focus().deleteRange(range).run();
			// Fires to TiptapEditor → opens ImageUploadModal (no prompt)
			window.dispatchEvent(new CustomEvent("tiptap-open-image-modal"));
		},
	},
	{
		title: "YouTube",
		description: "Embed a YouTube video",
		icon: "Youtube",
		group: "Media",
		command: ({ editor, range }) => {
			editor.chain().focus().deleteRange(range).run();
			// Fires to TiptapEditor → opens YoutubeModal (no prompt)
			window.dispatchEvent(new CustomEvent("tiptap-open-youtube-modal"));
		},
	},
	{
		title: "File Attachment",
		description: "Upload PDF, DOCX, etc.",
		icon: "FileText",
		group: "Media",
		command: ({ editor, range }) => {
			editor.chain().focus().deleteRange(range).run();
			window.dispatchEvent(new CustomEvent("tiptap-upload-file"));
		},
	},

	// ── Layout ──
	{
		title: "Table",
		description: "Add a data table",
		icon: "Table",
		group: "Layout",
		command: ({ editor, range }) =>
			editor
				.chain()
				.focus()
				.deleteRange(range)
				.insertTable({ rows: 3, cols: 3, withHeaderRow: true })
				.run(),
	},
	{
		title: "Quote",
		description: "Capture a quote",
		icon: "Quote",
		group: "Layout",
		command: ({ editor, range }) =>
			editor.chain().focus().deleteRange(range).toggleBlockquote().run(),
	},
	{
		title: "Callout",
		description: "Highlighted info block",
		icon: "MessageSquare",
		group: "Layout",
		command: ({ editor, range }) =>
			editor.chain().focus().deleteRange(range).setCallout().run(),
	},
	{
		title: "Divider",
		description: "Horizontal separator",
		icon: "Minus",
		group: "Layout",
		command: ({ editor, range }) =>
			editor.chain().focus().deleteRange(range).setHorizontalRule().run(),
	},

	// ── Advanced ──
	{
		title: "Code Block",
		description: "Syntax highlighted code",
		icon: "Code",
		group: "Advanced",
		command: ({ editor, range }) =>
			editor.chain().focus().deleteRange(range).toggleCodeBlock().run(),
	},
];

export default {
	items: ({ query }) =>
		COMMANDS.filter((item) =>
			item.title.toLowerCase().includes(query.toLowerCase()),
		).slice(0, 15),

	render: () => {
		let component;
		let popup;

		return {
			onStart: (props) => {
				component = new VueRenderer(CommandList, {
					props,
					editor: props.editor,
				});

				if (!props.clientRect) return;

				popup = tippy("body", {
					getReferenceClientRect: props.clientRect,
					appendTo: () => document.body,
					content: component.element,
					showOnCreate: true,
					interactive: true,
					trigger: "manual",
					placement: "bottom-start",
					animation: "shift-away",
				});
			},

			onUpdate(props) {
				component.updateProps(props);
				if (!props.clientRect) return;
				popup[0].setProps({ getReferenceClientRect: props.clientRect });
			},

			onKeyDown(props) {
				if (props.event.key === "Escape") {
					popup[0].hide();
					return true;
				}
				return component.ref?.onKeyDown(props);
			},

			onExit() {
				popup[0].destroy();
				component.destroy();
			},
		};
	},
};
