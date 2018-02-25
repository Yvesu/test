import React, { Component } from 'react'

import { Link } from 'react-router'

import { Menu } from 'antd'
const MenuItem = Menu.Item

class NavMenu extends Component {
  constructor(props){
    super(props)
    this.state={
      navMenu:[]
    }
    // console.log(this.props);
  }
  render(){
    return (
      <div className="topNavlist">
        <Menu theme="dark" mode="horizontal" defaultSelectedKeys={['0']}>
          {
            this.state.navMenu.map((value,index)=>{
              // console.log(value,index);
              return(
                <Menu.Item key={index}>
                  <Link to={value.uri}><span>{value.name}</span></Link>
                </Menu.Item>
              )
            })
          }
        </Menu>
      </div>
    )
  }
  componentDidMount(){
    fetch('./data/top_menu.json')
      .then((resonpe=>resonpe.json()))
      .then((res)=>{
        // console.log(res.uri);
        this.setState({
          navMenu:res
          // selects:res.uri
        })
      })
  }
}
export default NavMenu
