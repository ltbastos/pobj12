<script setup lang="ts">
import { computed } from 'vue'
import {
  IconChevronDown,
  IconChevronRight,
  IconChevronLeft,
  IconLogout2,
  IconDashboard,
  IconListTree,
  IconTrophy,
  IconChartLine,
  IconCalculator,
  IconSpeakerphone,
  IconSearch,
  IconX,
  IconCheck,
  IconCircleCheck,
  IconAlertCircle,
  IconAlertTriangle,
  IconAlertOctagon,
  IconInfoCircle,
  IconHome,
  IconArrowLeft,
  IconArrowUp,
  IconArrowDown,
  IconUser,
  IconUserCheck,
  IconUserMinus,
  IconUserPlus,
  IconUserCog,
  IconInbox,
  IconPlus,
  IconTicket,
  IconBulb,
  IconPaperclip,
  IconDeviceFloppy,
  IconArrowsMaximize,
  IconArrowsMinimize,
  IconArrowsUpDown,
  IconArrowsExchange,
  IconColumns,
  IconChevronsDown,
  IconChevronsUp,
  IconCalendar,
  IconClock,
  IconLock,
  IconShieldLock,
  IconBell,
  IconAdjustmentsHorizontal,
  IconAdjustmentsAlt,
  IconListCheck,
  IconMedal,
  IconCash,
  IconCoin,
  IconTarget,
  IconBoxMultiple,
  IconChartArcs,
  IconClipboardCheck,
  IconBuildingBank,
  IconMessagePlus,
  IconMessage,
  IconTrash,
  IconGripVertical,
  IconCircleX,
  IconCircle,
  IconFilter,
  IconRefresh,
  IconLoader2
} from '@tabler/icons-vue'

interface Props {
  name: string
  size?: number | string
  class?: string | Record<string, boolean> | Array<string | Record<string, boolean>>
  color?: string
  stroke?: number
}

const props = withDefaults(defineProps<Props>(), {
  size: 24,
  class: '',
  color: 'currentColor',
  stroke: 2
})

const iconMap: Record<string, any> = {
  'chevron-down': IconChevronDown,
  'chevron-right': IconChevronRight,
  'chevron-left': IconChevronLeft,
  'logout-2': IconLogout2,
  'dashboard': IconDashboard,
  'list-tree': IconListTree,
  'trophy': IconTrophy,
  'chart-line': IconChartLine,
  'calculator': IconCalculator,
  'speakerphone': IconSpeakerphone,
  'search': IconSearch,
  'x': IconX,
  'check': IconCheck,
  'circle-check': IconCircleCheck,
  'alert-circle': IconAlertCircle,
  'alert-triangle': IconAlertTriangle,
  'alert-octagon': IconAlertOctagon,
  'info-circle': IconInfoCircle,
  'home': IconHome,
  'arrow-left': IconArrowLeft,
  'arrow-up': IconArrowUp,
  'arrow-down': IconArrowDown,
  'user': IconUser,
  'user-check': IconUserCheck,
  'user-minus': IconUserMinus,
  'user-plus': IconUserPlus,
  'user-cog': IconUserCog,
  'inbox': IconInbox,
  'plus': IconPlus,
  'ticket': IconTicket,
  'bulb': IconBulb,
  'paperclip': IconPaperclip,
  'device-floppy': IconDeviceFloppy,
  'arrows-maximize': IconArrowsMaximize,
  'arrows-minimize': IconArrowsMinimize,
  'arrows-up-down': IconArrowsUpDown,
  'arrows-exchange': IconArrowsExchange,
  'columns': IconColumns,
  'chevrons-down': IconChevronsDown,
  'chevrons-up': IconChevronsUp,
  'calendar': IconCalendar,
  'clock': IconClock,
  'lock': IconLock,
  'shield-lock': IconShieldLock,
  'bell': IconBell,
  'adjustments-horizontal': IconAdjustmentsHorizontal,
  'adjustments-alt': IconAdjustmentsAlt,
  'list-check': IconListCheck,
  'medal': IconMedal,
  'cash': IconCash,
  'coin': IconCoin,
  'target': IconTarget,
  'box-multiple': IconBoxMultiple,
  'chart-arcs': IconChartArcs,
  'clipboard-check': IconClipboardCheck,
  'building-bank': IconBuildingBank,
  'message-plus': IconMessagePlus,
  'message': IconMessage,
  'trash': IconTrash,
  'grip-vertical': IconGripVertical,
  'circle-x': IconCircleX,
  'filter': IconFilter,
  'refresh': IconRefresh,
  'loader-2': IconLoader2,
  'circle': IconCircle
}

const iconSize = computed(() => {
  if (typeof props.size === 'number') {
    return props.size
  }
  return props.size
})

const IconComponent = computed(() => {
  return iconMap[props.name] || null
})
</script>

<template>
  <component
    v-if="IconComponent"
    :is="IconComponent"
    :size="iconSize"
    :color="color"
    :stroke="stroke"
    :class="props.class"
    :style="{ display: 'inline-block', verticalAlign: 'middle' }"
    aria-hidden="true"
  />
</template>

<style scoped>
:deep(svg) {
  display: inline-block !important;
  vertical-align: middle;
}
</style>

