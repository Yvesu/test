import React, { Component } from 'react'
import LoginForm from './LoginForm'

class Login extends Component{
  render(){
    return(
      <div className="home_content_pageLogin">
        <h3>登录</h3>
        <LoginForm />
        <p>登录HiVideo，即表示您已经阅读并同意我们的 <a href="" target="_blank">服务条款</a> 并了解我们的 <a href="" target="_blank">隐私政策</a> 。</p>
      </div>
    )
  }
}

export default Login
