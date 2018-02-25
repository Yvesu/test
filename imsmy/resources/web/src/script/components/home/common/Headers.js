import React, { Component } from 'react'
import { Layout,Button, Icon,Badge, Avatar } from 'antd'
const { Header } = Layout
import SearchHeader from './SearchHeader'
import Notice from './Notice'
import UserList from './UserList'

class Headers extends Component{
  constructor(props){
    super(props)
    this.state = {

    }
    // console.log(this.props.collapsed,'hhahf');
  }

  render(){
    return(
      <Header className={this.props.collapsed===false? "home_page_index_header" : "home_page_index_header_collapsed"}>
        <Icon
          className="trigger"
          type={this.props.collapsed===true ? 'menu-unfold' : 'menu-fold'}
          onClick={this.props.toggle}
        />
        <div className="header_right_set">
          <SearchHeader />
          <Notice />
          <UserList />
        </div>
      </Header>
    )
  }
}

export default Headers
