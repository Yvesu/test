import React, { Component } from 'react'

import { Link } from 'react-router'

import { Menu } from 'antd'
const MenuItem = Menu.Item

class NavMenu extends Component {
  constructor(props){
    super(props)
    this.state={
      navMenu:[],
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
      <div className="topNavlist">
        <Menu theme="dark" mode="horizontal" onClick={this.handleClick} selectedKeys={[this.state.current]}>
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
        this.setState({
          navMenu:res
        })
      })
  }
}
export default NavMenu
