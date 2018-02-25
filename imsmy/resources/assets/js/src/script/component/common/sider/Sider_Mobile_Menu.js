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
            <Menu theme="dark" defaultSelectedKeys={['0']} defaultOpenKeys={['sub0']} mode="inline" >
              <Menu.Item key='0'>
                <Link to='/terminal'><Icon type='calendar' /><span>设备终端</span></Link>
              </Menu.Item>
                <SubMenu key='sub0' title={<span><Icon type='calendar' /><span>滤镜</span></span>}>
                      <Menu.Item key='1'>
                          <Link to='/filterAll'><span>全部</span></Link>
                      </Menu.Item>
                      <Menu.Item key='2'>
                        <Link to='/filterRelease'><Icon type='calendar' /><span>发布滤镜</span></Link>
                      </Menu.Item>

                </SubMenu>



            </Menu>
        </Sider>
    )
  }
  // componentDidMount(){
  //   fetch('./data/mobile_sider_menu.json')
  //     .then((resonpe=>resonpe.json()))
  //     .then((res)=>{
  //       // console.log(res);
  //       this.setState({
  //         siderMenu:res
  //         // selects:res.uri
  //       })
  //     })
  // }
}
export default SiderMenu
