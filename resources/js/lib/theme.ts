import { cn } from './utils'

const enterExit = 'data-[state=open]:animate-in data-[state=closed]:animate-out'
const fade = 'data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0'
const zoom = 'data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95'
const slideFromSide =
    'data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2'
const overlayMotion = `${enterExit} ${fade}`
const popoverMotion = `${overlayMotion} ${zoom} ${slideFromSide}`
// Reka's tooltip content reports `delayed-open`/`instant-open` instead of `open`,
// so `data-[state=open]` never matches there. The enter animation therefore has to
// run unconditionally on mount and only the exit keys off `data-[state=closed]`.
const tooltipMotion = `animate-in fade-in-0 zoom-in-95 data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=closed]:zoom-out-95 ${slideFromSide}`
// One canonical trailing action row, shared by dialog footers and form actions.
const actionRow = 'flex flex-col-reverse gap-2 sm:flex-row sm:justify-end'
// One canonical split row that stacks on small screens, shared by page headers and table toolbars.
const splitRow = 'flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between'
// Table head and body cells differ only in text alignment, so the shared half lives here.
const tableHeadCell = 'h-12 px-4 align-middle font-medium text-muted-foreground'
const tableBodyCell = 'p-4'

export const appTheme = {
    animation: {
        fadeTransition: 'transition-opacity duration-200',
        fadeHidden: 'opacity-0'
    },
    button: {
        base: "inline-flex shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-control text-sm font-medium transition-[color,background-color,border-color,box-shadow,opacity] outline-none disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive aria-invalid:ring-destructive/20",
        variant: {
            default: 'bg-primary text-primary-foreground shadow-surface hover:bg-primary/90',
            destructive: 'bg-destructive text-destructive-foreground shadow-surface hover:bg-destructive/90 focus-visible:ring-destructive/25',
            outline: 'border border-input bg-background shadow-surface hover:bg-accent hover:text-accent-foreground',
            secondary: 'bg-secondary text-secondary-foreground shadow-surface hover:bg-secondary/80',
            ghost: 'hover:bg-accent hover:text-accent-foreground',
            link: 'text-primary underline-offset-4 hover:underline'
        },
        size: {
            default: 'h-9 px-4 py-2 has-[>svg]:px-3',
            sm: 'h-8 gap-1.5 px-3 has-[>svg]:px-2.5',
            lg: 'h-10 px-6 has-[>svg]:px-4',
            icon: 'size-9'
        }
    },
    surface: {
        card: 'flex flex-col gap-6 rounded-panel border border-border bg-card py-6 text-card-foreground shadow-surface',
        panel: 'rounded-panel border border-border bg-card text-card-foreground shadow-surface',
        glass: 'border border-glass-border bg-glass shadow-glass backdrop-blur-xl supports-[backdrop-filter]:bg-glass',
        danger: 'rounded-panel border border-destructive/30 bg-destructive/10'
    },
    overlay: `fixed inset-0 z-50 bg-overlay ${overlayMotion}`,
    dialog: {
        content: `fixed top-1/2 left-1/2 z-50 grid w-full max-w-[calc(100%-2rem)] -translate-x-1/2 -translate-y-1/2 gap-4 rounded-panel border border-border bg-background p-6 text-foreground shadow-floating duration-200 ${overlayMotion} ${zoom} sm:max-w-lg`,
        scrollContent:
            'relative z-50 my-8 grid w-full max-w-lg gap-4 rounded-panel border border-border bg-background p-6 text-foreground shadow-floating duration-200 md:w-full',
        close: "absolute top-4 right-4 rounded-control opacity-70 transition-opacity hover:bg-accent hover:text-accent-foreground hover:opacity-100 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background focus-visible:outline-none disabled:pointer-events-none [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4",
        footer: actionRow
    },
    sheet: {
        content: `fixed z-50 flex flex-col gap-4 bg-background text-foreground shadow-floating transition ease-in-out ${enterExit} data-[state=closed]:duration-300 data-[state=open]:duration-500`,
        close: 'absolute top-4 right-4 rounded-control opacity-70 transition-opacity hover:bg-accent hover:text-accent-foreground hover:opacity-100 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background focus-visible:outline-none disabled:pointer-events-none'
    },
    floating: {
        content: `z-50 max-h-(--reka-dropdown-menu-content-available-height) min-w-32 overflow-x-hidden overflow-y-auto rounded-floating border border-border bg-popover p-1 text-popover-foreground shadow-floating ${popoverMotion}`,
        item: "relative flex cursor-default items-center gap-2 rounded-control px-2 py-1.5 text-sm outline-hidden select-none focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50 data-[inset]:pl-8 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 [&_svg:not([class*='text-'])]:text-muted-foreground",
        destructive: 'text-destructive focus:bg-destructive/10 focus:text-destructive [&_svg]:text-destructive!',
        subTrigger: 'data-[state=open]:bg-accent data-[state=open]:text-accent-foreground',
        separator: '-mx-1 my-1 h-px bg-border',
        label: 'px-2 py-1.5 text-sm font-medium data-[inset]:pl-8'
    },
    field: {
        control:
            'w-full rounded-control border border-input bg-transparent text-foreground shadow-surface transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive aria-invalid:ring-destructive/20',
        input: 'flex h-9 min-w-0 px-3 py-1 text-base selection:bg-primary selection:text-primary-foreground file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground md:text-sm',
        textarea: 'flex min-h-16 px-3 py-2 text-base md:text-sm',
        multiselect: 'min-h-24 px-3 py-2 text-sm',
        toggle: 'flex items-center justify-between gap-4 rounded-panel border border-border bg-card/50 p-3',
        checkbox:
            'peer size-4 shrink-0 rounded-sm border border-input shadow-surface outline-none transition-shadow data-[state=checked]:border-primary data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive aria-invalid:ring-destructive/20',
        radio: 'aspect-square size-4 rounded-full border border-input text-primary shadow-surface outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive aria-invalid:ring-destructive/20',
        switch: 'inline-flex h-5 w-9 shrink-0 items-center rounded-full border border-transparent shadow-surface outline-none transition-all data-[state=checked]:bg-primary data-[state=unchecked]:bg-input focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50',
        switchThumb:
            'pointer-events-none block size-4 rounded-full bg-background ring-0 transition-transform data-[state=checked]:translate-x-4 data-[state=unchecked]:translate-x-0',
        tabsList: 'inline-flex h-9 w-fit items-center justify-center rounded-panel bg-muted p-0.75 text-muted-foreground',
        tabsTrigger:
            'inline-flex h-[calc(100%-1px)] flex-1 items-center justify-center rounded-control border border-transparent px-2 py-1 text-sm font-medium whitespace-nowrap outline-none transition-[color,box-shadow] data-[state=active]:bg-background data-[state=active]:text-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:pointer-events-none disabled:opacity-50',
        description: 'text-xs text-muted-foreground',
        error: 'text-sm text-destructive'
    },
    form: {
        actions: actionRow
    },
    layout: {
        pageHeader: splitRow
    },
    select: {
        trigger:
            "flex w-fit items-center justify-between gap-2 whitespace-nowrap rounded-control border border-input bg-transparent px-3 py-2 text-sm shadow-surface transition-[color,box-shadow] outline-none data-[placeholder]:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive aria-invalid:ring-destructive/20 data-[size=default]:h-9 data-[size=sm]:h-8 *:data-[slot=select-value]:line-clamp-1 *:data-[slot=select-value]:flex *:data-[slot=select-value]:items-center *:data-[slot=select-value]:gap-2 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 [&_svg:not([class*='text-'])]:text-muted-foreground",
        content: `relative z-50 max-h-(--reka-select-content-available-height) min-w-32 overflow-x-hidden overflow-y-auto rounded-floating border border-border bg-popover text-popover-foreground shadow-floating ${popoverMotion}`,
        popper: 'data-[side=bottom]:translate-y-1 data-[side=left]:-translate-x-1 data-[side=right]:translate-x-1 data-[side=top]:-translate-y-1',
        viewport: 'p-1',
        popperViewport: 'h-[var(--reka-select-trigger-height)] w-full min-w-[var(--reka-select-trigger-width)] scroll-my-1',
        item: "relative flex w-full cursor-default items-center gap-2 rounded-control py-1.5 pr-8 pl-2 text-sm outline-hidden select-none focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50 *:[span]:last:flex *:[span]:last:items-center *:[span]:last:gap-2 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 [&_svg:not([class*='text-'])]:text-muted-foreground"
    },
    pagination: {
        wrapper: 'mt-2 w-full overflow-x-auto pb-1',
        navigation: 'gap-1 px-2.5 sm:pr-2.5',
        page: 'h-9 w-9 p-0 sm:h-10 sm:w-10'
    },
    table: {
        mobileGrid: 'grid gap-3 md:hidden',
        mobileCard: 'rounded-panel border border-border bg-card p-3 text-card-foreground shadow-surface',
        mobileEmpty: 'rounded-panel border border-dashed border-border bg-card/50 p-6 text-center text-sm text-muted-foreground',
        headerCell: `${tableHeadCell} text-left`,
        actionsHeaderCell: `${tableHeadCell} text-right`,
        actionsCell: `${tableBodyCell} text-right`,
        sortableHeader: 'inline-flex items-center gap-1 rounded-control focus-visible:ring-2 focus-visible:ring-ring/60 focus-visible:outline-none',
        row: 'border-b border-border transition-colors hover:bg-muted/50',
        cell: tableBodyCell,
        empty: 'p-8 text-center text-muted-foreground',
        toolbar: splitRow
    },
    toast: {
        base: 'group pointer-events-auto relative flex w-full items-center justify-between gap-4 overflow-hidden rounded-panel border p-4 pr-8 text-foreground shadow-floating transition-all',
        close: 'absolute top-2 right-2 rounded-control p-1 text-foreground/70 transition-colors hover:bg-accent hover:text-foreground',
        variant: {
            default: 'border-border bg-background',
            success: 'border-success/40 bg-success/10',
            error: 'border-destructive/40 bg-destructive/10',
            info: 'border-info/40 bg-info/10',
            warning: 'border-warning/40 bg-warning/10'
        }
    },
    status: {
        success: 'text-success',
        error: 'text-destructive',
        info: 'text-info',
        warning: 'text-warning'
    },
    navigation: {
        trigger:
            'group inline-flex h-9 w-max items-center justify-center rounded-control bg-background px-4 py-2 text-sm font-medium transition-[color,box-shadow] outline-none hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:pointer-events-none disabled:opacity-50 data-[state=open]:bg-accent/50 data-[state=open]:text-accent-foreground',
        sidebarButton:
            'peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-control p-2 text-left text-sm outline-hidden ring-sidebar-ring transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground focus-visible:ring-2 active:bg-sidebar-accent active:text-sidebar-accent-foreground disabled:pointer-events-none disabled:opacity-50 group-has-data-[sidebar=menu-action]/menu-item:pr-8 aria-disabled:pointer-events-none aria-disabled:opacity-50 data-[active=true]:bg-sidebar-accent data-[active=true]:font-medium data-[active=true]:text-sidebar-accent-foreground data-[state=open]:hover:bg-sidebar-accent data-[state=open]:hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:size-8! group-data-[collapsible=icon]:pr-2! [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0',
        sidebarVariant: {
            default: 'hover:bg-sidebar-accent hover:text-sidebar-accent-foreground',
            outline:
                'border border-sidebar-border bg-background shadow-surface hover:border-sidebar-accent hover:bg-sidebar-accent hover:text-sidebar-accent-foreground'
        },
        sidebarSize: {
            default: 'h-8 text-sm',
            sm: 'h-7 text-xs',
            lg: 'h-12 text-sm group-data-[collapsible=icon]:p-0!'
        },
        footerItem: 'text-muted-foreground hover:text-foreground',
        marketingHeader: 'flex h-16 items-center gap-3 rounded-2xl px-4 sm:px-5',
        marketingLogo: 'inline-flex shrink-0 items-center rounded-panel px-2 py-1.5 transition-colors hover:bg-muted/70',
        marketingActive: 'bg-primary/10 text-primary hover:bg-primary/15',
        marketingIdle: 'text-muted-foreground hover:bg-muted/80 hover:text-foreground',
        content:
            'top-0 left-0 w-full p-2 pr-2.5 data-[motion^=from-]:animate-in data-[motion^=to-]:animate-out data-[motion^=from-]:fade-in data-[motion^=to-]:fade-out data-[motion=from-end]:slide-in-from-right-52 data-[motion=from-start]:slide-in-from-left-52 data-[motion=to-end]:slide-out-to-right-52 data-[motion=to-start]:slide-out-to-left-52 md:absolute md:w-auto group-data-[viewport=false]/navigation-menu:top-full group-data-[viewport=false]/navigation-menu:mt-1.5 group-data-[viewport=false]/navigation-menu:overflow-hidden group-data-[viewport=false]/navigation-menu:rounded-floating group-data-[viewport=false]/navigation-menu:border group-data-[viewport=false]/navigation-menu:border-border group-data-[viewport=false]/navigation-menu:bg-popover group-data-[viewport=false]/navigation-menu:text-popover-foreground group-data-[viewport=false]/navigation-menu:shadow-floating group-data-[viewport=false]/navigation-menu:duration-200 group-data-[viewport=false]/navigation-menu:data-[state=open]:animate-in group-data-[viewport=false]/navigation-menu:data-[state=closed]:animate-out group-data-[viewport=false]/navigation-menu:data-[state=closed]:zoom-out-95 group-data-[viewport=false]/navigation-menu:data-[state=open]:zoom-in-95 group-data-[viewport=false]/navigation-menu:data-[state=open]:fade-in-0 group-data-[viewport=false]/navigation-menu:data-[state=closed]:fade-out-0 **:data-[slot=navigation-menu-link]:focus:ring-0 **:data-[slot=navigation-menu-link]:focus:outline-none',
        viewport: `relative mt-1.5 h-[var(--reka-navigation-menu-viewport-height)] w-full origin-top-center overflow-hidden rounded-floating border border-border bg-popover text-popover-foreground shadow-floating ${enterExit} data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-90 md:w-[var(--reka-navigation-menu-viewport-width)]`,
        link: "flex flex-col gap-1 rounded-control p-2 text-sm outline-none transition-[color,box-shadow] hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground focus-visible:ring-3 focus-visible:ring-ring/20 data-[active=true]:bg-accent/50 data-[active=true]:text-accent-foreground [&_svg:not([class*='size-'])]:size-4 [&_svg:not([class*='text-'])]:text-muted-foreground"
    },
    feedback: {
        status: 'text-sm font-medium',
        empty: 'flex flex-col items-center justify-center py-12 text-center',
        emptyIcon: 'mb-4 flex size-16 items-center justify-center rounded-full bg-muted text-muted-foreground',
        emptyTitle: 'text-lg font-semibold text-foreground',
        emptyDescription: 'mt-2 max-w-sm text-sm text-muted-foreground',
        loadingOverlay: 'flex flex-col items-center justify-center gap-3 bg-background/80 backdrop-blur-sm',
        loadingText: 'text-sm font-medium text-muted-foreground',
        spinnerSize: {
            sm: 'size-4',
            md: 'size-6',
            lg: 'size-8',
            xl: 'size-12'
        },
        spinnerColor: {
            primary: 'text-primary',
            inverse: 'text-primary-foreground',
            muted: 'text-muted-foreground'
        }
    },
    link: 'text-foreground underline decoration-border underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current!',
    tooltip: `z-50 w-fit rounded-control bg-primary px-3 py-1.5 text-xs text-balance text-primary-foreground ${tooltipMotion}`,
    marketing: {
        glowTop: 'bg-[image:var(--marketing-glow-top)]',
        glowBottom: 'bg-[image:var(--marketing-glow-bottom)]',
        badge: 'inline-flex w-fit items-center rounded-full border border-border bg-card/80 px-3 py-1 text-xs font-medium tracking-wide text-muted-foreground',
        featureCard: 'border-border bg-card/90 shadow-surface'
    }
} as const

export type ButtonVariant = keyof typeof appTheme.button.variant
export type ButtonSize = keyof typeof appTheme.button.size
export type SidebarButtonVariant = keyof typeof appTheme.navigation.sidebarVariant
export type SidebarButtonSize = keyof typeof appTheme.navigation.sidebarSize
export type ToastVariant = keyof typeof appTheme.toast.variant
export type StatusTone = keyof typeof appTheme.status

export const buttonStyles = (options: { variant?: ButtonVariant | undefined; size?: ButtonSize | undefined } = {}): string => {
    const { variant = 'default', size = 'default' } = options

    return cn(appTheme.button.base, appTheme.button.variant[variant], appTheme.button.size[size])
}

export const sidebarButtonStyles = (
    options: {
        variant?: SidebarButtonVariant | undefined
        size?: SidebarButtonSize | undefined
    } = {}
): string => {
    const { variant = 'default', size = 'default' } = options

    return cn(appTheme.navigation.sidebarButton, appTheme.navigation.sidebarVariant[variant], appTheme.navigation.sidebarSize[size])
}

export const toastStyles = (variant: ToastVariant = 'default'): string => cn(appTheme.toast.base, appTheme.toast.variant[variant])
