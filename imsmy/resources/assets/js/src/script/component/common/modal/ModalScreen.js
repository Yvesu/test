import React, { Component } from 'react'
import { Modal, Button, Form, Select, Input } from 'antd'

const FormItem = Form.Item
const Option = Select.Option
const { TextArea } = Input

class ModalScreen extends Component {
	constructor(props){
    super(props)
     this.state = {
      loading: false,
      visible: this.props.visible,
  	}
  }
  handleOk(){
		// console.log(this.props.id)
		const selectId = this.props.id
    this.setState({ loading: true })
    // setTimeout(() => {
      this.setState({ loading: false, visible: false })
    // }, 2000)
  }
  handleCancel(){
    this.setState({ visible: false })
  }
	showModalScreen(){
		this.setState({
			visible:true
		})
		// console.log(this.props.id)
    // console.log(this.props.SelectAll)
		return false
	}
  render() {
    const { visible, loading } = this.state
    return (
      <div>
      	<Button style={{display:"block",color:"#ff0000"}} onClick={this.showModalScreen.bind(this)}>屏蔽</Button>
        <Modal
          visible={visible}
          title="屏蔽视频"
          onOk={this.handleOk}
          onCancel={this.handleCancel.bind(this)}
          footer={[
            <Button key="back" size="large" onClick={this.handleCancel.bind(this)}>取消</Button>,
            <Button key="submit" type="primary" size="large" loading={loading} onClick={this.handleOk.bind(this)}>
              确定
            </Button>,
          ]}
        >
          <Form>
            <FormItem label="预置理由" labelCol={{ span: 4 }} wrapperCol={{ span: 8 }} >
                <Select defaultValue="请选择" >
                  <Option value="reason1">违反平台协议</Option>
                  <Option value="reason2">其他</Option>
                </Select>
            </FormItem>
            <FormItem label="屏蔽理由" labelCol={{ span: 4 }} wrapperCol={{ span: 18 }} >
              <TextArea placeholder="" defaultValue="请输入至少五个字符" rows={4}/>
            </FormItem>
          </Form>
        </Modal>
      </div>
    );
  }
}

export default ModalScreen
