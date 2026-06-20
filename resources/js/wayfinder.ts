export type RouteQueryOptions = {
	query?: Record<string, unknown>;
	mergeQuery?: Record<string, unknown>;
};

export type RouteDefinition<M extends string = string> = {
	url: string;
	method: M;
};

export type RouteFormDefinition<M extends string = string> = {
	action: string;
	method: M;
};

export function queryParams(options?: RouteQueryOptions): string {
	const query = options?.mergeQuery ?? options?.query;
	if (!query) return "";

	const params = new URLSearchParams();

	for (const [key, value] of Object.entries(query)) {
		if (value === undefined || value === null) continue;
		params.set(key, String(value));
	}

	const serialized = params.toString();
	return serialized ? `?${serialized}` : "";
}

export function createRoute(
	path: string,
	method: string = "get",
) {
	const route = (options?: RouteQueryOptions) => ({
		url: route.url(options),
		method,
	});

	route.definition = {
		methods: [method],
		url: path,
	} as const;

	route.url = (options?: RouteQueryOptions) => path + queryParams(options);
	route.form = (options?: RouteQueryOptions) => ({
		action: route.url(options),
		method,
	});
	route.get = (options?: RouteQueryOptions) => ({
		url: route.url(options),
		method: "get",
	});
	route.post = (options?: RouteQueryOptions) => ({
		url: route.url(options),
		method: "post",
	});
	route.put = (options?: RouteQueryOptions) => ({
		url: route.url(options),
		method: "put",
	});
	route.patch = (options?: RouteQueryOptions) => ({
		url: route.url(options),
		method: "patch",
	});
	route.delete = (options?: RouteQueryOptions) => ({
		url: route.url(options),
		method: "delete",
	});

	return route as any;
}
