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
            <Menu theme="dark" defaultSelectedKeys={['0']} defaultOpenKeys={['sub1']} mode="inline" >
              <Menu.Item key='0'>
                  <Link to='/manage'><Icon type='desktop' /><span>管理信息</span></Link>
              </Menu.Item>
              <SubMenu key='sub1' title={<span><Icon type='calendar' /><span>视频</span></span>}>
                <Menu.Item key='1'>
                  <Link to='/videoCensor'><span>视频审查</span></Link>
                </Menu.Item>
                <Menu.Item key='2'>
                  <Link to='/videoPending'><span>待定池</span></Link>
                </Menu.Item>
                <Menu.Item key='3'>
                  <Link to='/notPass'><span>未通过</span></Link>
                </Menu.Item>
                <Menu.Item key='4'>
                  <Link to='/videoScreen'><span>屏蔽仓</span></Link>
                </Menu.Item>
              </SubMenu>
              <SubMenu key='sub2' title={<span><Icon type='trophy' /><span>竞赛</span></span>}>
                <Menu.Item key='5'>
                  <Link to='/race'><span>竞赛审核</span></Link>
                </Menu.Item>
                <Menu.Item key='6'>
                  <Link to='/notPass'><span>未通过</span></Link>
                </Menu.Item>
                <Menu.Item key='7'>
                  <Link to='/screen'><span>屏蔽仓</span></Link>
                </Menu.Item>
              </SubMenu>
              <SubMenu key='sub3' title={<span><Icon type='folder-open' /><span>素材</span></span>}>
                <Menu.Item key='8'>
                  <Link to='/material'><span>素材审查</span></Link>
                </Menu.Item>
                <Menu.Item key='9'>
                  <Link to='/notPass'><span>未通过</span></Link>
                </Menu.Item>
                <Menu.Item key='10'>
                  <Link to='/screen'><span>屏蔽仓</span></Link>
                </Menu.Item>
              </SubMenu>
              <Menu.Item key='11'>
                  <Link to='/audit'><Icon type='safety' /><span>认证审核</span></Link>
              </Menu.Item>
              <Menu.Item key='12'>
                  <Link to='/apply'><Icon type='pay-circle' /><span>提现申请</span></Link>
              </Menu.Item>
              <Menu.Item key='13'>
                  <Link to='/emcee'><Icon type='pay-circle' /><span>主持人申请</span></Link>
              </Menu.Item>
              <Menu.Item key='14'>
                  <Link to='/complain'><Icon type='question-circle' /><span>投诉与反馈</span></Link>
              </Menu.Item>
              <Menu.Item key='15'>
                  <Link to='/shieldset'><Icon type='minus-circle' /><span>屏蔽理由设置</span></Link>
              </Menu.Item>
              <Menu.Item key='16'>
                  <Link to='/retrieve'><Icon type='delete' /><span>回收站</span></Link>
              </Menu.Item>
            </Menu>
        </Sider>
    )
  }
  // componentDidMount(){
  //   fetch('./data/station_side_Menu.json')
  //     .then((resonpe)=>resonpe.json())
  //     .then((res)=>{
  //       this.setState({
  //         siderMenu:res
  //       })
  //     })
  // }
}
export default SiderMenu
