export interface SuggestionItem {
	name: string;
	slug?: string;
	category?: string;
}

export const categorySuggestions: SuggestionItem[] = [
	{ name: "Web Development", slug: "html5" },
	{ name: "Mobile Development", slug: "android" },
	{ name: "UI/UX Design", slug: "figma" },
	{ name: "Graphic Design", slug: "photoshop" },
	{ name: "Game Development", slug: "unity" },
	{ name: "Machine Learning / AI", slug: "python" },
	{ name: "Cyber Security", slug: "linux" },
	{ name: "Database Design", slug: "mysql" },
];

export const toolsSuggestions: SuggestionItem[] = [
	{ name: "Figma", category: "design" },
	{ name: "VS Code", category: "dev" },
	{ name: "Python", category: "dev" },
	{ name: "GitHub", category: "dev" },
	{ name: "PostgreSQL", category: "database" },
	{ name: "MySQL", category: "database" },
	{ name: "MATLAB", category: "math" },
	{ name: "Android Studio", category: "dev" },
	{ name: "Docker", category: "dev" },
	{ name: "Laravel", category: "dev" },
	{ name: "Notion", category: "comms" },
	{ name: "Photoshop", category: "design" },
	{ name: "Illustrator", category: "design" },
	{ name: "Premiere Pro", category: "design" },
];

export const getToolSlug = (toolName: string): string => {
	const name = toolName.toLowerCase().trim();
	if (name === "figma") return "figma";
	if (name === "photoshop" || name === "adobe photoshop" || name === "ps")
		return "photoshop";
	if (name === "illustrator" || name === "adobe illustrator" || name === "ai")
		return "illustrator";
	if (
		name === "premiere" ||
		name === "premiere pro" ||
		name === "pr" ||
		name === "premierepro"
	)
		return "premiere";
	if (
		name === "vs code" ||
		name === "vscode" ||
		name === "visual studio code" ||
		name === "visual-studio-code"
	)
		return "visual-studio-code";
	if (name === "visual studio" || name === "vs") return "visual-studio";
	if (
		name === "vue" ||
		name === "vue.js" ||
		name === "vuejs" ||
		name === "vuedotjs"
	)
		return "vue";
	if (name === "react" || name === "reactjs" || name === "react.js")
		return "react";
	if (
		name === "tailwind" ||
		name === "tailwindcss" ||
		name === "tailwind css" ||
		name === "tailwind-css"
	)
		return "tailwind-css";
	if (name === "laravel") return "laravel";
	if (name === "php") return "php";
	if (name === "javascript" || name === "js") return "javascript";
	if (name === "html" || name === "html5") return "html5";
	if (name === "css" || name === "css3") return "css";
	if (name === "git") return "git";
	if (name === "github") return "github";
	if (name === "docker") return "docker";
	if (name === "postman") return "postman";
	if (name === "canva") return "canva";
	if (name === "trello") return "trello";
	if (name === "jira") return "jira";
	if (name === "sass" || name === "scss") return "sass";
	if (name === "nodejs" || name === "node" || name === "node.js")
		return "nodedotjs";
	if (name === "typescript" || name === "ts") return "typescript";
	if (name === "python") return "python";
	if (name === "mysql") return "mysql";
	if (name === "postgresql" || name === "postgres") return "postgresql";
	if (name === "mongodb" || name === "mongo") return "mongodb";
	if (name === "firebase") return "firebase";
	if (name === "flutter") return "flutter";
	if (name === "kotlin") return "kotlin";
	if (name === "swift") return "swift";
	if (name === "xd" || name === "adobe xd") return "adobe-xd";
	if (name === "indesign" || name === "adobe indesign") return "adobe-indesign";
	if (
		name === "after effects" ||
		name === "ae" ||
		name === "adobe after effects"
	)
		return "adobe-after-effects";

	return name
		.replaceAll(".js", "dotjs")
		.replaceAll(".net", "dotnet")
		.replaceAll(/[^a-z0-9]+/g, "-");
};
