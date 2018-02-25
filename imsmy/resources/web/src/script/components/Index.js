import React, { Component } from 'react'
import { Layout } from 'antd'
// import { }



class Index extends Component {

  render(){
    return(
      <Layout style={{overflow:"hidden"}}>
         {this.props.children}
      </Layout>
    )
  }
  componentWillMount(){
    // console.log(localStorage.getItem('TOKEN'),'index');
    if(localStorage.getItem('TOKEN')===null){
      // message.error('请您登录！！！')
      this.props.router.push('/home')
    }
  }
}

export default Index
