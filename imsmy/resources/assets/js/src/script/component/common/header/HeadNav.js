import React, { Component } from 'react'

import { Layout} from 'antd'
const { Header } = Layout

import NavMenu from './NavMenu'
import RightMenu from './RightMenu'

class HeaderNav extends Component {
  constructor(props){
    super(props)

  }
  // console.log(this.refs.navmenu);
  render(){
    return (
      <Header className="headNav">
        <div className="Logo">
          <span>HI</span>
          <span>管理控制台</span>
        </div>
        <NavMenu />
        <RightMenu />
      </Header>
    )
  }
}

export default HeaderNav
