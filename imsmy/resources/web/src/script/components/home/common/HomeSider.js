import React, { Component } from 'react'
import { Link,hashHistory } from 'react-router'
import { Layout,Menu, Icon } from 'antd'
const { Sider } = Layout
class HomeSider extends Component {
  constructor(props){
    super(props)
    this.state={
      selectKey:'opus'
    }
  }
  handleChangeKey=(e)=>{
    this.setState({
      selectKey:e.key
    })
  }
  render(){
    const bigLogo = 'http://img.cdn.hivideo.com/web/img/logo.png'
    const smallLogo = 'http://img.cdn.hivideo.com/web/img/hivideo_logo.png'
    // const bigLogo='./img/logo.png'
    // const smallLogo='./img/hivideo_logo.png'
    // console.log(this.props.collapsed,'collapsed');
    return(
      <Sider
        trigger={null}
        collapsible
        collapsed={this.props.collapsed}
        className="home_page_index_sider"
      >
        <div className={this.props.collapsed===false? "logo bigLogo" : "logo smallLogo"}>
          <img src={this.props.collapsed===false? bigLogo : smallLogo } alt="logo" />
        </div>
        <Menu theme="dark" mode="inline" onClick={this.handleChangeKey} selectedKeys={[this.state.selectKey]}>
          <Menu.Item key="opus">
          <Link to='/opus'>
          <i className="anticon sider_children_icon">
            {/* <img src="./img/opus.png" /> */}
            <img src="http://img.cdn.hivideo.com/web/img/opus.png" />

          </i>
             <span>作品</span>
          </Link>
          </Menu.Item>
          <Menu.Item key="race">
            <Link to='/race'>
              <i className="anticon sider_children_icon">
                {/* <img src="./img/race.png" /> */}
                <img src="http://img.cdn.hivideo.com/web/img/race.png" />
              </i>
              <span>竞赛</span>
            </Link>
          </Menu.Item>
        </Menu>
      </Sider>
    )


  }
  componentDidMount(){
    const pathName =this.props.pathName.split('/')[1]
    this.setState({
      selectKey:pathName
    })
  }
}

export default HomeSider
