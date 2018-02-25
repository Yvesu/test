import React, { Component } from 'react'
import { hashHistory,Route,Redirect } from 'react-router'
import { Layout, message,Spin } from 'antd'
// const { Content } = Layout
import HeadNav from './common/header/HeadNav'
// import SiderMenu from './common/sider/SiderMenu'

import Login from './login/Login'
class Index extends Component {
  constructor(props){
    super(props)
    // this.state={
    //   loading:false
    // }
  }
  render(){
    return(
        <Layout style={{overflow:"hidden"}}>
          <HeadNav />
          {this.props.children}
        </Layout>

    )
  }
  componentWillMount(){
    // this.setState({
    //   loading:true
    // })
    if(localStorage.getItem('TOKEN')===null){
      message.info('您还没有登录')
      this.props.router.push('/login')
    }
  }
  componentDidMount(){
  //   <Spin spinning={this.state.loading}>
  // </Spin>
    // setTimeout(()=>{
    //     this.setState({
    //       loading:false
    //     })
    //   },2000)
    // this.setState({
    //   loading:false
    // })
  }

}

export default Index
