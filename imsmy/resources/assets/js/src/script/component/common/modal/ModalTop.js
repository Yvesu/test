import React, { Component } from 'react'
import { Modal, Button, message, Select } from 'antd'
const confirm = Modal.confirm
import Fetch from 'utils/fetch'

class ModalTop extends Component {
	constructor(props){
    super(props)
     this.state = {
	    loading: false,
	    visible: this.props.visible,
			topOption:[],
			selectId:[],
			chooseTime:"",
	  }

  }
  handleOk=()=>{
		this.setState({ loading: true})
		if(this.state.chooseTime!==''){
			let formData = new FormData()
			if(this.state.selectId.length !== undefined && this.state.selectId.length===1){
				this.state.selectId = this.state.selectId.toString()
				formData.append('id',this.state.selectId)
			}else if(this.state.selectId.length>1){
				this.state.selectId = this.state.selectId.join("|")
				formData.append('id',this.state.selectId)
			}else{
				formData.append('id',this.state.selectId)
			}
			formData.append('time',this.state.chooseTime)
			Fetch.post({
				uri:this.props.uri,
				callback:(res)=>{
					if(res.message && res.message==='修改成功'){
						message.success('置顶成功')
						this.setState({ loading: false, visible: false })
						if(this.props.RefreshTableState){
							this.props.RefreshTableState(true)
						}
					}else{
						message.error(res.message||res.error);
						this.setState({ loading: false, visible: false })
						if(this.props.RefreshTableState){
							this.props.RefreshTableState(true)
						}
					}
				},
				formData:formData
			})
		}else{
			message.warning('请选择时间')
			this.setState({ loading: false})
		}
  }
  handleCancel=()=>{
		message.success('已取消')
    this.setState({ visible: false });
  }

	showModal=()=>{
		if(this.props.btnName==="置顶"){
			this.setState({
				visible:true,
				selectId:this.props.id
			})
		}else{
			this.setState({
				selectId:this.props.id
			})
			this.handleModalCancelTop()
		}
		return false;
	}

	handleModalCancelTop=()=>{
		confirm({
    title: '取消置顶',
    content: '确定取消置顶吗？',
    okText: '确认',
    cancelText: '取消',
		onOk:()=>{
			Fetch.post({
				uri:`${this.props.uri}?id=${this.state.selectId}`,
				callback:(res)=>{
					if(res.message && res.message==='修改成功'){
						message.success('取消置顶成功')
						if(this.props.RefreshTableState){
							this.props.RefreshTableState(true)
						}
					}else{
						message.error(res.message || res.error)
						if(this.props.RefreshTableState){
							this.props.RefreshTableState(true)
						}
					}
				}
			})
		},
		onCancel:()=>{
			message.warning('已取消')
		}
  	});
	}

	AddSelectOptions=()=>{
		Fetch.post({
			uri:'/api/admins/fodder/fragment/ishottime',
			callback:(res)=>{
				// console.log(res.data);
				this.setState({
					topOption:res.data
				})
			}
		})
	}
	handleChangeExpireTime=(value)=>{
		this.setState({
			chooseTime:value
		})
	}
  //字体颜色 ,color:"#108ee9"
  render() {
    const { visible, loading } = this.state
    return (
      <div>
      	<Button onClick={this.showModal}>{this.props.btnName}</Button>
        <Modal title={"设置置顶过期时间"}
          visible={visible}
          onOk={this.handleOk}
          onCancel={this.handleCancel}
          footer={[
            <Button key="back" size="large" onClick={this.handleCancel}>取消</Button>,
            <Button key="submit" type="primary" size="large" loading={loading} onClick={this.handleOk}>
              确定
            </Button>
          ]}
        >
					<div className="top_select_expire_time">
						<span>预选时间:</span>
						<Select className="select_expire_choose_box"
							placeholder="请选择时间"
							onFocus={this.AddSelectOptions}
							onChange={this.handleChangeExpireTime}
							>
								{
									this.state.topOption.map((value,index)=>{
										return(
											<Option value={value.label} key={index}>{value.des}</Option>
										)
									})
								}
							</Select>
					</div>
        </Modal>
      </div>
    )
  }
}

export default ModalTop
