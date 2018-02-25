import React, { Component } from 'react'
import { Modal, Button, Checkbox, Input, message,Layout  } from 'antd'
import { uploadButtonImg } from '../../common/uploads/UploadBtn'
import UploadsImg from '../../common/uploads/UploadsImg'
import FileType from '../../common/uploads/UploadFileType'
import Fetch from 'utils/fetch'

class ModalSort extends Component {
	constructor(props){
    super(props)
     this.state = {
	    loading: false,
	    visible: this.props.visible,
			inputValue:'',
			coverUrl:'',
			checkValue:false
	  }
  }
  handleOk=()=>{
		if(this.state.inputValue ===''){
			message.error('请输入分类名称');
		}else if(this.state.coverUrl === ''){
			message.error('请选择封面图片');
		} else{
			console.log(this.state.inputValue);
			this.setState({ loading: true });
			let formData = new FormData()
			formData.append('name',this.state.inputValue)
			formData.append('type_icon',this.state.coverUrl)
			if(this.state.checkValue === false){
				formData.append('active',0)
			}else{
				formData.append('active',1)
			}
			Fetch.post({
				uri:'/api/admins/fodder/template/add/type',
				callback:(res)=>{
					console.log(res);
					if(res.data){
						message.success('创建新分类成功')
						setTimeout(() => {
							this.setState({ loading: false, visible: false })
						}, 3000);
					}else{
						message.error(res.error);
						this.setState({ loading: false})
					}
				},
				formData:formData
			})

		}
  }
  handleCancel=()=>{
    this.setState({ visible: false });
  }

	showModal=()=>{
		this.setState({
			visible:true
		})
		return false;
	}
	handleChangeInput=(e)=>{
		// console.log(this.refs.test.props.placeholder);
		this.setState({
			inputValue:e.target.value
		})

	}
	handleChangeUpImg=(value)=>{
		console.log(value)
			this.setState({
				coverUrl:value
			})
	}
	handleChangeCheckStart=(e)=>{
		this.setState({
			checkValue:e.target.checked
		})
		console.log(this.state.checkValue,'54');
	}

  //字体颜色 ,color:"#108ee9"
  render() {
    const { visible, loading } = this.state
    return (
      <div>
      	<Button type="primary" onClick={this.showModal}>创建新分类</Button>
        <Modal
          visible={visible}
          title={<p style={{color:'#666'}}>新建分类
						<span style={{color:'#999',fontSize:'12px',marginLeft:'8px'}}>
							填写信息
						</span></p>}
          onOk={this.handleOk}
          onCancel={this.handleCancel}
          footer={[
						<Checkbox style={{float:'left'}}
							onChange={this.handleChangeCheckStart}>立即启用</Checkbox>,
            <Button key="back" size="large" onClick={this.handleCancel}>取消</Button>,
            <Button key="submit" type="primary" size="large" loading={loading} onClick={this.handleOk}>
              确定
            </Button>
          ]}
        >
					<Layout className="new_sort_modal">
						<div>
							<span>分类名称:</span>
							<p><Input placeholder="请输入名称" type="text"
									value={this.state.inputValue} onChange={this.handleChangeInput}
							/></p>
						</div>
						<div><span>分类图标:</span>
							<p>
								<UploadsImg uploadButton={uploadButtonImg} beforeUpload={FileType.beforeUploadImg}
									name={this.props.name}
									onChange={this.handleChangeUpImg}
								/>
							</p>
						</div>
						<p>支持扩展名：.png .jpg .gif</p>
					</Layout>
        </Modal>
      </div>
    )
  }
}

export default ModalSort
