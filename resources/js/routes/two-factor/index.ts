import { createRoute } from "../../wayfinder";

export const enable = createRoute("/user/two-factor-authentication", "post");
export const disable = createRoute("/user/two-factor-authentication", "delete");
export const recoveryCodes = createRoute("/user/two-factor-recovery-codes", "get");
export const regenerateRecoveryCodes = createRoute("/user/two-factor-recovery-codes", "post");
