import React, { Component } from 'react'
import { Layout, Breadcrumb } from 'antd'
const { Content } = Layout

import SiderMenu from '../common/sider/Sider_Contents_Menu'

//系统设置页

class SystemSet extends Component {
  constructor(props){
    super(props)
  }
  render(){
    return(
      <Layout className="content_box">

          <Layout>
              <Breadcrumb routes={this.props.routes} params={this.props.params} />
            <Content>
                {this.props.children || "系统设置页"}
            </Content>
          </Layout>
      </Layout>
    )
  }
}

export default SystemSet
