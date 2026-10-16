import type { Component } from 'vue'
import type { SharedPageData } from './app-data'

export interface BreadcrumbItem {
    title: string
    href: string
}

export type NavItemActiveMatch = 'exact' | 'prefix'

export interface NavItem {
    title: string
    href: string
    icon?: Component
    isActive?: boolean
    activeMatch?: NavItemActiveMatch
    external?: boolean
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & SharedPageData

export interface SelectOption {
    value: string
    label: string
}
