import ThemeTogglerButton from "./ThemeTogglerButton.vue";

export { ThemeTogglerButton };
export type ThemeTogglerButtonProps = {
  variant?: 'default' | 'accent' | 'destructive' | 'outline' | 'secondary' | 'ghost';
  size?: 'default' | 'sm' | 'lg';
  direction?: 'btt' | 'ttb' | 'ltr' | 'rtl';
  modes?: ('light' | 'dark' | 'system' | 'auto')[];
};
