import React, { Component } from 'react'
import { Link } from 'react-router'

import { Layout, Menu, Icon} from 'antd'
const { Sider } = Layout
const SubMenu = Menu.SubMenu

class Sider_User_Menu extends Component {
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
            <Menu theme="dark" defaultSelectedKeys={['0']} defaultOpenKeys={['1']} mode="inline" >
              {/* {
                this.state.siderMenu.map((value,index)=>{
                  if(value.children && value.children.length){
                    return(
                      <SubMenu key={index} title={<span><Icon type={value.icon} /><span>{value.name}</span></span>}>
                        {
                          value.children.map((value,index) =>{
                            return(
                              <Menu.Item key={value.index}>
                                  <Link to={value.uri}><span>{value.name}</span></Link>
                              </Menu.Item>
                            )
                          })
                        }
                      </SubMenu>
                    )
                  }
                  return(
                    <Menu.Item key={index}>
                        <Link to={value.uri}><Icon type={value.icon} /><span>{value.name}</span></Link>
                    </Menu.Item>
                  )
                })
              } */}
              <Menu.Item key='0'>
                  <Link to='/userMonitor'><span>监控页</span></Link>
              </Menu.Item>
              {/* <Icon type={value.icon} /> */}
              <SubMenu key='1' title={<span><span>用户管理</span></span>}>
                  <Menu.Item key='2'>
                      <Link to='/manageAll'><span>全部</span></Link>
                  </Menu.Item>
                  <Menu.Item key='3'>
                      <Link to='/manageThird'><span>第三方</span></Link>
                  </Menu.Item>
                  <Menu.Item key='4'>
                      <Link to='/manageMechanism'><span>机构</span></Link>
                  </Menu.Item>
                  <Menu.Item key='5'>
                      <Link to='/manageVip'><span>VIP</span></Link>
                  </Menu.Item>
                  <Menu.Item key='6'>
                      <Link to='/manageFramer'><span>创作者</span></Link>
                  </Menu.Item>
                  <Menu.Item key='7'>
                      <Link to='/manageCensor'><span>审查者</span></Link>
                  </Menu.Item>
                  <Menu.Item key='8'>
                      <Link to='/manageVerify'><span>认证用户</span></Link>
                  </Menu.Item>
                  <Menu.Item key='9'>
                      <Link to='/manageChoice'><span>精选用户</span></Link>
                  </Menu.Item>
                  <Menu.Item key='10'>
                      <Link to='/manageFrozen'><span>冻结仓</span></Link>
                  </Menu.Item>


              </SubMenu>
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
  // }
}
export default Sider_User_Menu
