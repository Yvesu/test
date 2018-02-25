import React, { Component } from 'react'
import { hashHistory } from 'react-router'
import {Form,Input, Button,Row, Col,message} from 'antd'
const FormItem = Form.Item
import Fetch from 'utils/fetch'
class LoginForms extends Component{
  constructor(props){
    super(props)
    this.state = {

    }
  }
  handleSubmit = (e) => {
    e.preventDefault();
    this.props.form.validateFields((err, values) => {
      if (!err) {
        // console.log('Received values of form: ', values);
        let UserName = values.userName
        let PassWord = values.password
        // fetch()
        let formData = new FormData()
        formData.append('name',UserName)
        formData.append('password',PassWord)
        Fetch.post({
          uri:'/api/testlogin',
          callback:(res)=>{
            // console.log(res);
            if(res.token){
              let TOKEN = res.token
              localStorage.setItem('TOKEN',res.token)
              message.success('登录成功！！！');
              hashHistory.push('/index')
            }else{
              message.error('登录失败！！！用户名或密码错误');
            }
          },
          formData:formData
        })
      }
    });
  }
  render(){
    const { getFieldDecorator } = this.props.form

    return(
      <Form onSubmit={this.handleSubmit} className="login-form">
        <Row gutter={32}>
          <Col span={12}>
            <FormItem
              label='账户'
              colon={false}
              >
              {getFieldDecorator('userName', {
                rules: [{ required: true, message: '请输入账户' }],
              })(
                <Input  placeholder="请输入账户" />
              )}
            </FormItem>
          </Col>
          <Col span={12}>
            <FormItem
              label='密码'
              colon={false}
            >
              {getFieldDecorator('password', {
                rules: [{ required: true, message: '请输入密码' }],
              })(
                <Input  type="password" placeholder="请输入密码" />
              )}
            </FormItem>
          </Col>
        </Row>
          <FormItem>
            <Button type="primary" htmlType="submit" size='large' className="login-form-button login_form_btn">
              登 录
            </Button>

          </FormItem>
        </Form>
      );
    }
    componentDidMount(){

      if(localStorage.getItem('TOKEN')!==null){
        // message.error('请您登录！！！')
        hashHistory.push('/index')
      }
    }
  }

  const LoginForm = Form.create()(LoginForms);

  export default LoginForm
