import React, { Component } from 'react'
import { Layout,Button, message } from 'antd'
const { Header } = Layout
class Headers extends Component{
  constructor(props){
    super(props)
    this.state = {
      showBtn:true
    }
  }
  handleChangeBtn = ()=>{
    this.props.handleChage()
    this.setState({
      showBtn:false
    })
  }
  goBackShowBtn =()=>{
    this.props.goBackIndex()
    this.setState({
      showBtn:true
    })
  }

  render(){
    return(
      <Header className='index_header_box'>
        <div className='header_index_logo' onClick={this.goBackShowBtn}>
          {/* <img src='./img/logo.png'/> */}
          <img src='http://img.cdn.hivideo.com/web/img/logo.png'/>
        </div>
        {this.state.showBtn === true? <div className="header_btn_box">
          <span>我已有HiVideo账户</span>
          <Button onClick={this.handleChangeBtn} className="header_login_btn">登录</Button>
        </div> : <p>没有账号？<a href="#" onClick={()=>{message.info('暂未开放,敬请期待!')}}>免费注册</a></p>
      }
      </Header>
    )
  }
}

export default Headers
