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
  componentWillReceiveProps(nextPorps){
    // console.log(this.props.getKey);
    if(this.props.getKey == '' || this.props.getKey == "0"){
      fetch('./data/station_side_Menu.json')
      .then((resonpe=>resonpe.json()))
      .then((res)=>{
        this.setState({
          siderMenu:res
        })
      })
    }if(this.props.getKey == "1"){
      fetch('./data/contents_sider_menu.json')
      .then((resonpe=>resonpe.json()))
      .then((res)=>{
        this.setState({
          siderMenu:res
        })
      })
    }
  }

  render(){

    return(
        <Sider className="sider_box"
          collapsible
          collapsed={this.state.collapsed}
          onCollapse={this.onCollapse.bind(this)}>
            <Menu theme="dark" defaultSelectedKeys={['0']} defaultOpenKeys={['1']} mode="inline" >
              {
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
              }

            </Menu>
        </Sider>
    )
  }
  componentDidMount(){
    fetch('./data/station_side_Menu.json')
    .then((resonpe=>resonpe.json()))
    .then((res)=>{
      this.setState({
        siderMenu:res
      })
    })
  }
}
export default SiderMenu
