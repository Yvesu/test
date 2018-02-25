import React, { Component } from 'react'
import { Link } from 'react-router'

import { Layout, Menu, Icon} from 'antd'
const { Sider } = Layout
const SubMenu = Menu.SubMenu

class SiderMenu extends Component {
  constructor(props){
    super(props)
    this.state={
      collapsed:false,
      siderMenu:[]
    }
    // console.log(this.props);
  }
  onCollapse(collapsed){
     this.setState({ collapsed });
   }

  render(){
    const subMenu=this.state.siderMenu
    // console.log(this.props)
    return(
        <Sider className="sider_box"
          collapsible
          collapsed={this.state.collapsed}
          onCollapse={this.onCollapse.bind(this)}>
            <Menu theme="dark" defaultSelectedKeys={['0']} defaultOpenKeys={['0']} mode="inline" >
                <Menu.Item key='0'>
                    <Link to='/hostSearch'><Icon type='calendar' /><span>搜索热点</span></Link>
                </Menu.Item>
            </Menu>
        </Sider>
    )
  }
  // componentDidMount(){
  //   fetch('./data/contents_sider_menu.json')
  //     .then((resonpe=>resonpe.json()))
  //     .then((res)=>{
  //       // console.log(res);
  //       this.setState({
  //         siderMenu:res
  //         // selects:res.uri
  //       })
  //     })
  // // }
}
export default SiderMenu
