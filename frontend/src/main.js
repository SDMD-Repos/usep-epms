import { createApp } from 'vue'
import PerfectScrollbar from 'vue3-perfect-scrollbar'

import App from './App.vue'
import './registerServiceWorker'
import router from './router'
import store from './store'
import { i18n } from './localization'
import { filters } from './filters'

import {
  Avatar, TreeSelect, InputNumber, Button, Layout, Table, Radio, Dropdown, Menu, Input,
  Badge, Slider, Form, Tooltip, Select, Switch, Descriptions, DescriptionsItem, Alert,
  Spin, Checkbox, Tabs, Drawer, Divider, AutoComplete, Collapse, Card, List,
  Tree, Row, Col, Modal, Popconfirm, ConfigProvider, message, notification,
} from 'ant-design-vue'

const app = createApp(App)
  .use(store)
  .use(router)
  .use(i18n)
  .use(PerfectScrollbar)
  .use(Avatar)
  .use(Popconfirm)
  .use(Modal)
  .use(Divider)
  .use(Row)
  .use(Col)
  .use(Tree)
  .use(List)
  .use(Card)
  .use(Button)
  .use(TreeSelect)
  .use(Layout)
  .use(Table)
  .use(Radio)
  .use(Dropdown)
  .use(Menu)
  .use(Input)
  .use(Badge)
  .use(Slider)
  .use(Form)
  .use(Tooltip)
  .use(Select)
  .use(Spin)
  .use(Checkbox)
  .use(Tabs)
  .use(InputNumber)
  .use(Drawer)
  .use(Switch)
  .use(Descriptions)
  .use(DescriptionsItem)
  .use(Alert)
  .use(AutoComplete)
  .use(Collapse)
  .use(ConfigProvider)

app.config.globalProperties.$filters = filters
app.provide('a-message', message)
app.provide('a-notification', notification)

app.mount('#app')

