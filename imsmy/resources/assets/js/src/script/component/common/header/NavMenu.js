import React, { Component } from 'react'

import { Link } from 'react-router'

import { Menu,Layout } from 'antd'
const MenuItem = Menu.Item

class NavMenu extends Component {
  constructor(props){
    super(props)
    this.state={
      // navMenu:[],
      current:"0"
    }
    // console.log(this.props);
  }
  handleClick = (e) => {
    // console.log('click ', e);
    // console.log(e.key)
    this.setState({
      current: e.key,
    })

  }
  render(){
    // console.log(onselectKey);
    return (
      <Layout className="topNavlist">
        <Menu theme="dark" mode="horizontal" onClick={this.handleClick} selectedKeys={[this.state.current]}>

          <Menu.Item key='0'>
            <Link to='/station'><span>站务</span></Link>
          </Menu.Item>
          <Menu.Item key='1'>
            <Link to='/contents'><span>内容</span></Link>
          </Menu.Item>
          <Menu.Item key='2'>
            <Link to='/sourceMaterial'><span>素材</span></Link>
          </Menu.Item>
          <Menu.Item key='3'>
            <Link to='/advert'><span>广告</span></Link>
          </Menu.Item>
          <Menu.Item key='4'>
            <Link to='/user'><span>用户</span></Link>
          </Menu.Item>
          <Menu.Item key='5'>
            <Link to='/finance'><span>财务</span></Link>
          </Menu.Item>
          <Menu.Item key='6'>
            <Link to='/mobile'><span>移动端</span></Link>
          </Menu.Item>
          <Menu.Item key='7'>
            <Link to='/systemSet'><span>系统设置</span></Link>
          </Menu.Item>
        </Menu>
      </Layout>
    )
  }
  componentDidMount(){
    fetch('./data/top_menu.json')
      .then((resonpe=>resonpe.json()))
      .then((res)=>{
        this.setState({
          navMenu:res
        })
      })
  }
}
export default NavMenu
