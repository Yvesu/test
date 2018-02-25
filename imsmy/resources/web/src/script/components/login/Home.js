import React, { Component } from 'react'
import { Layout } from 'antd'
const { Content } =Layout
import Headers from './common/Headers'
import Footers from './common/Footers'
import LoginHome from './LoginHome'
import Login from './Login'

class Home extends Component{
  constructor(props){
    super(props)
    this.state =  {
      changeLogin:false
    }
  }
  handleChangeLogin = ()=>{
    this.setState({
      changeLogin:true
    })
  }
  handleGoBackIndex = ()=>{
    this.setState({
      changeLogin:false
    })
  }
  render(){
    return(
      <Layout className="m_index">
        <Headers handleChage={this.handleChangeLogin} goBackIndex={this.handleGoBackIndex}/>
        <Content className='content_box'>
          {this.state.changeLogin!==true? <LoginHome /> : <Login />}
        </Content>
        <Footers />
      </Layout>
    )
  }
}

export default Home
