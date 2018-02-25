import React, { Component } from 'react'
import { Layout, Breadcrumb } from 'antd'
const { Content } = Layout

import SiderMenu from '../common/sider/Sider_Mobile_Menu'

//移动端

class Mobile extends Component {
  constructor(props){
    super(props)
  }
  render(){
    return(
      <Layout className="content_box">
        <SiderMenu />
          <Layout>
              <Breadcrumb routes={this.props.routes} params={this.props.params} />
            <Content>
                {this.props.children || "移动端"}
            </Content>
          </Layout>
      </Layout>
    )
  }
}

export default Mobile
