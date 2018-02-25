import React, { Component } from 'react'
import { Layout,Button,Menu, Icon } from 'antd'
const { Header, Sider, Content,Footer } = Layout
import HomeSider from './common/HomeSider'
import Headers from './common/Headers'
class HomePage extends Component{
  constructor(props){
    super(props)
    this.state={
      collapsed: false,
    }
  }
  toggle = () => {
    this.setState({
      collapsed: !this.state.collapsed,
    });
    console.log(this.state.collapsed);
  }
  render(){
    // console.log(this.props);
    return(
      <Layout className="home_page_index" style={{position:"relative"}}>
        <HomeSider collapsed={this.state.collapsed} pathName={this.props.location.pathname}/>
        <Layout style={{background:'#fff'}}>
          <Headers collapsed={this.state.collapsed} toggle={this.toggle}/>
          <Content style={{ margin:"65px 10px 10px 10px", padding: 10, background: '#fff', height:"auto !important"}}>
            {this.props.children}
          </Content>
          <Footer style={{color:"#4a4a4a",textAlign:"center"}}>
            Copyright ©  Hivideo.com All Rights Reserved | 京ICP备15066211号
          </Footer>
        </Layout>

      </Layout>
    )
  }
  // componentDidMount(){
  //   console.log('homepage');
  // }
}

export default HomePage
