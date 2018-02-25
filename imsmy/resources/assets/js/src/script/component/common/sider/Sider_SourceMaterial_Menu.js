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
          <Menu theme="dark" defaultSelectedKeys={['0']} defaultOpenKeys={['sub1']} mode="inline" >
            <Menu.Item key='0'>
                <Link to='/platform'><Icon type='desktop' /><span>平台看板</span></Link>
            </Menu.Item>
            <SubMenu key='sub1' title={<span><Icon type='calendar' /><span>模板</span></span>}>
              <Menu.Item key='1'>
                <Link to='/templateAll'><span>全部</span></Link>
              </Menu.Item>
              <Menu.Item key='2'>
                <Link to='/templateSort'><span>分类</span></Link>
              </Menu.Item>
            </SubMenu>
            <SubMenu key='sub2' title={<span><Icon type='trophy' /><span>片段</span></span>}>
              <Menu.Item key='3'>
                <Link to='/fragmentAll'><span>全部</span></Link>
              </Menu.Item>
              <Menu.Item key='4'>
                <Link to='/fragmentRecommend'><span>推荐</span></Link>
              </Menu.Item>
              <Menu.Item key='5'>
                <Link to='/fragmentClassify'><span>分类</span></Link>
              </Menu.Item>
              <Menu.Item key='6'>
                <Link to='/fragmentSearch'><span>搜索热点</span></Link>
              </Menu.Item>
              <Menu.Item key='7'>
                <Link to='/fragmentUpload'><span>上传片段</span></Link>
              </Menu.Item>
              <Menu.Item key='8'>
                <Link to='/fragmentScreen'><span>屏蔽仓</span></Link>
              </Menu.Item>
            </SubMenu>
            <SubMenu key='sub3' title={<span><Icon type='trophy' /><span>混合资源库</span></span>}>
              <Menu.Item key='9'>
                <Link to='/libraryAll'><span>全部</span></Link>
              </Menu.Item>
              <Menu.Item key='10'>
                <Link to='/libraryRecommend'><span>推荐</span></Link>
              </Menu.Item>
              <Menu.Item key='11'>
                <Link to='/libraryClassify'><span>分类</span></Link>
              </Menu.Item>
              <Menu.Item key='12'>
                <Link to='/librarySearch'><span>搜索热点</span></Link>
              </Menu.Item>
              <Menu.Item key='13'>
                <Link to='/libraryUpload'><span>上传资源</span></Link>
              </Menu.Item>
              <Menu.Item key='14'>
                <Link to='/libraryScreen'><span>屏蔽仓</span></Link>
              </Menu.Item>
            </SubMenu>
          </Menu>
      </Sider>
    )
  }
  // componentDidMount(){
  //   fetch('./data/sourceMaterial_sider_menu.json')
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
