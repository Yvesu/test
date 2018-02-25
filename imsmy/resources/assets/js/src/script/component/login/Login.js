import React, { Component } from 'react'
import { hashHistory } from 'react-router'
import {Layout, Form, Icon, Input, Button, Checkbox, message, Spin } from 'antd';
const FormItem = Form.Item;
import Fetch from 'utils/fetch'
class LoginFrom extends React.Component {
  constructor(props){
    super(props)
    this.state={
      loading:true,
    }
  }
  handleSubmit = (e) => {
    e.preventDefault();
    this.props.form.validateFields((err, values) => {
      if (!err) {
        console.log('Received values of form: ', values);
        let formData = new FormData()
        formData.append('email',values.email)
        formData.append('password',values.password)
        Fetch.post({
          uri:'/api/admins/sign',
          callback:(res)=>{
            // console.log(res,'login');
            if(res.token){
              let TOKEN = res.token
              localStorage.setItem('TOKEN',res.token)
              message.success('登录成功！！！');
              hashHistory.push('/station')
            }else{
              message.error('登录失败！！！用户名或密码错误');
            }
          },
          formData:formData
        })
      }
    });
  }
  render() {
    const { getFieldDecorator } = this.props.form;
    return (
      <div className="login_box">
        <Spin spinning={this.state.loading} size="large">
        <div className="login_logo">
          <img  src="./logo.png" />
        </div>
        <Form onSubmit={this.handleSubmit}>
          <FormItem hasFeedback>
            {getFieldDecorator('email', {
              rules: [{
              type: 'email', message: '用户名格式错误',
            }, { required: true, message: '请输入您的用户名!' }],
            })(
              <Input size="large" prefix={<Icon type="user" />} placeholder="用户名"  className='login_username' />
            )}
          </FormItem>
          <FormItem hasFeedback>
            {getFieldDecorator('password', {
              rules: [{ required: true, message: '请输入您的密码!' }],
            })(
              <Input size="large" prefix={<Icon type="lock" />} type="password" placeholder="密码" className='login_password' />
            )}
          </FormItem>
          <FormItem>
            {getFieldDecorator('remember', {
              valuePropName: 'checked',
              initialValue: true,
            })(
              <Checkbox>记住我</Checkbox>
            )}
            <a className="login-form-forgot">忘记密码</a>
            <Button size="large" type="primary" htmlType="submit" className="login-form-button">
              登录
            </Button>
          </FormItem>
        </Form>
      </Spin>
    </div>

    )
  }
componentDidMount(){
    this.setState({
      loading:false
    })
    if(localStorage.getItem('TOKEN')){
      message.error('您已经登录，不需要重新登录！！！');
      this.props.router.push('/station')
    }
  }

}

const Login = Form.create()(LoginFrom);
export default Login
