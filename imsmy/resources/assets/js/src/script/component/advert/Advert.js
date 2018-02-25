import React, { Component } from 'react'
import { Layout, Breadcrumb } from 'antd'
const { Content } = Layout

import SiderMenu from '../common/sider/Sider_Contents_Menu'

//广告

class Finance extends Component {
  constructor(props){
    super(props)
  }
  render(){
    return(
      <Layout className="content_box">

          <Layout>
              <Breadcrumb routes={this.props.routes} params={this.props.params} />
            <Content>
                {this.props.children || '广告'}
            </Content>
          </Layout>
      </Layout>
    )
  }
}

export default Finance
