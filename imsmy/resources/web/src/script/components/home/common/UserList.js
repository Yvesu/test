import React, { Component } from 'react'
import { hashHistory } from 'react-router'
import { Menu, Dropdown, Icon, Avatar } from 'antd'



class UserList extends Component{
  constructor(props){
    super(props)
    this.state={

    }
  }
  handleChangeUserList=({key})=>{
    // console.log(e);
    if(key==='3'){
      // console.log(1);
    localStorage.removeItem('TOKEN')
      hashHistory.push('/home')
    }
  }

  render(){
    const menu = (
      <Menu className="user_list_drop_menu" onClick={this.handleChangeUserList}>
        <Menu.Item key="0">
          个人信息
        </Menu.Item>
        <Menu.Item key="1">
          用户列表
        </Menu.Item>
        <Menu.Divider />
        <Menu.Item key="3" >退出登录</Menu.Item>
      </Menu>
    )
    return(
      <div className="header_user_list">
        <Dropdown overlay={menu} trigger={['hover']} >
          <a className="ant-dropdown-link" href="javascript:void(0)">
            <Avatar src="https://zos.alipayobjects.com/rmsportal/ODTLcjxAfvqbxHnVXCYX.png" />
            <span className="user_name_and_icon">
              <b>用户昵称</b>
              <Icon type="caret-down" />
            </span>
          </a>
        </Dropdown>

      </div>

    )
  }
}
export default UserList
