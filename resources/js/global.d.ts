import Echo from "laravel-echo";

declare global {
	interface Window {
		Broadcaster: Echo;
	}
}
