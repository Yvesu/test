import React, { Component } from 'react'
import { Layout, Breadcrumb } from 'antd'
const { Content } = Layout

import SiderMenu from '../common/sider/Sider_SourceMaterial_Menu'

//素材页

class SourceMaterial extends Component {
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
                {this.props.children}
            </Content>
          </Layout>
      </Layout>
    )
  }
}

export default SourceMaterial
