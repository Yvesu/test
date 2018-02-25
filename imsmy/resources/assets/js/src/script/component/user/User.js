import React, { Component } from 'react'
import { Layout, Breadcrumb } from 'antd'
const { Content } = Layout

import SiderMenu from '../common/sider/Sider_User_Menu'

//用户页

class User extends Component {
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

export default User
