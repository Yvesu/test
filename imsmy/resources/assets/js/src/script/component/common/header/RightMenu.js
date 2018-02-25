import React, { Component } from 'react'
import { hashHistory } from 'react-router'
import { Menu, Icon, Badge,Button,Layout } from 'antd'
const { SubMenu } = Menu
import FetchPost from 'utils/fetch'

class RightMenu extends Component {
  constructor(props){
    super(props)
    // console.log(hashHistory);
    this.state={
      userName:''
    }
  }
  SignOut(){
    localStorage.removeItem('TOKEN')
    localStorage.removeItem('username')
    // console.log(localStorage);
    // console.log(hashHistory.push);
    hashHistory.push('/login')
  }

    render(){
      return(
        <Layout className="rightSubMenu" >
          <Menu theme="dark" mode="horizontal">
            <SubMenu title={<span><Icon type="mail" />消息<Badge count={25} style={{marginBottom:"5px",marginLeft:"5px"}} /></span>}>
                <Menu.Item key="mail:1">mail 1</Menu.Item>
                <Menu.Item key="mail:2">mail 2</Menu.Item>
                <Menu.Item key="mail:3">mail 3</Menu.Item>
                <Menu.Item key="mail:4">mail 4</Menu.Item>
            </SubMenu>
            <SubMenu title={<span className="user_info_name"><Icon type="user" />{this.state.userName}</span>}>
                <Menu.Item key="1"><span onClick={this.SignOut.bind(this)} className="user_info_out">退出</span></Menu.Item>

            </SubMenu>
            <SubMenu title={<span><Icon type="global" />简体中文</span>}>
                <Menu.Item key="global:1">global 1</Menu.Item>
                <Menu.Item key="global:2">global 2</Menu.Item>
                <Menu.Item key="global:3">global 3</Menu.Item>
                <Menu.Item key="global:4">global 4</Menu.Item>
            </SubMenu>
          </Menu>
        </Layout>

      )
    }
  componentDidMount(){
    FetchPost.post({
      uri:'/api/admins/manage',
      callback:(res)=>{
        // console.log(res.admin);
        this.setState({
          userName:res.admin.name
        })
      }
    })

  }
}
export default  RightMenu
