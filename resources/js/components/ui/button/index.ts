import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Button } from "./Button.vue"

export const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-xl text-sm font-semibold transition-all duration-200 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 shrink-0 [&_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/30 focus-visible:ring-2 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive",
  {
    variants: {
      variant: {
        default:
          "bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/20 hover:-translate-y-0.5 hover:shadow-blue-500/30 active:scale-[0.98] dark:from-[#214FAF] dark:to-[#0F6FBE] dark:shadow-[0_14px_34px_-18px_rgba(8,15,30,0.84)] dark:hover:shadow-[0_18px_38px_-18px_rgba(8,15,30,0.92)]",
        destructive:
          "bg-rose-600 text-white shadow-lg shadow-rose-500/20 hover:-translate-y-0.5 hover:bg-rose-700 hover:shadow-rose-500/30 active:scale-[0.98] focus-visible:ring-rose-500/20 dark:focus-visible:ring-rose-400/30",
        outline:
          "border border-wims-border/60 bg-wims-card text-slate-700 shadow-sm hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 dark:bg-slate-800/40 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-700/30 dark:hover:text-slate-100",
        secondary:
          "bg-slate-100 text-slate-700 shadow-sm hover:bg-slate-200 hover:text-slate-900 dark:bg-slate-800/60 dark:text-slate-200 dark:hover:bg-slate-700/80",
        ghost: "hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-700/50",
        link: "text-primary underline-offset-4 hover:underline",
      },
      size: {
        default: "h-10 px-4 py-2 has-[>svg]:px-3",
        sm: "h-8 rounded-xl gap-1.5 px-3 has-[>svg]:px-2.5",
        lg: "h-11 rounded-xl px-6 has-[>svg]:px-4",
        icon: "size-9",
        "icon-sm": "size-8",
        "icon-lg": "size-10",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  },
)
export type ButtonVariants = VariantProps<typeof buttonVariants>
