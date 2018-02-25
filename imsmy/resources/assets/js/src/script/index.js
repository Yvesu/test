require('../style/index.scss')

import 'antd/lib/style/v2-compatible-reset'
import 'ant-design-pro/dist/ant-design-pro.css'
import { LocaleProvider } from 'antd';
import zhCN from 'antd/lib/locale-provider/zh_CN'

import React from 'react'
import ReactDOM from 'react-dom'
import {Router, hashHistory, browserHistory} from 'react-router'


//抽离路由的文件
import routes from './route/routes'

ReactDOM.render(
  <LocaleProvider locale={zhCN}>{routes}</LocaleProvider>
  ,document.getElementById('root')
)
