import React, { Component } from 'react'
import { Layout, Breadcrumb } from 'antd'
const { Content } = Layout

import SiderMenu from '../common/sider/Sider_Station_Menu'

//站务

class Station extends Component {
  constructor(props){
    super(props)

  }
  render(){
    return(
      <Layout className="content_box">
          <SiderMenu />
          <Layout>
              <Breadcrumb routes={this.props.routes} params={this.props.params} />
            <Content style={{overflow:'hidden'}}>
                {this.props.children}
            </Content>
          </Layout>
      </Layout>
    )
  }
}

export default Station
