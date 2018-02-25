require('../style/index.scss')

import React from 'react'
import ReactDOM from 'react-dom'
import { Router, Route, hashHistory,IndexRedirect,IndexRoute } from 'react-router'
import { LocaleProvider } from 'antd';
import zhCN from 'antd/lib/locale-provider/zh_CN'
import 'ant-design-pro/dist/ant-design-pro.css'
import Index from './components/Index'
import Home from './components/login/Home'
import HomePage from './components/home/HomePage'
import Opus from './components/home/works/Opus'
import Race from './components/home/race/Race'
import UploadOpus from './components/home/works/uploadOpus/UploadOpus'


ReactDOM.render(
  <LocaleProvider locale={zhCN}>
    <Router onUpdate={()=>window.scrollTo(0,0)} history={hashHistory}>
      <Route path='/' component={Index}>
        <IndexRedirect to="/index" />
        <Route path="home" component={Home} />
        <Route path="index" component={HomePage}>
          <IndexRedirect to="/opus(/:type)" />
          <Route path="/opus(/:type)" component={Opus} />
          <Route path="/race" component={Race} />
        </Route>
      </Route>
    </Router>
  </LocaleProvider>,
  document.getElementById('webRoot')
)
