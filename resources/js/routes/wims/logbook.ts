import { createRoute } from "../../wayfinder";

export const store = createRoute("/wims/logbook", "post");
export const update = (logbook: string | number) => createRoute(`/wims/logbook/${logbook}`, "put");

export default {
	store,
	update,
};
