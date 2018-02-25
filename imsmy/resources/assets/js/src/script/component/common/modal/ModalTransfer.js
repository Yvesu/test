import React, { Component } from 'react'
import { Modal, Button, message } from 'antd'

import PopTransfer from './PopTransfer.js'
import Fetch from 'utils/fetch'
class ModalTransfer extends Component {
	constructor(props){
    super(props)
     this.state = {
	    loading: false,
	    visible: this.props.visible,
			popId:[],
			selectId:[]
	  }
		// console.log(fragmentAll,'popmod');

  }
  handleOk=()=>{
		this.setState({ loading: true });
		const selectId = this.state.selectId
		const popId = this.state.popId
		if(popId.length!=0){

			let formData = new FormData()
			if(selectId.length>1){
				const newSelectId = selectId.join("|")
				formData.append('id',newSelectId)
			}else{
				formData.append('id',selectId)
			}
			if(popId.length>1){
				const newPopId = popId.join('|')
				formData.append('type',newPopId)
			}else{
				formData.append('type',popId)
			}
			Fetch.post({
				uri:this.props.uri,
				callback:(res)=>{
					console.log(res,'分类成功返回值');
					if(res.message){
						message.success('分类成功')
						if(this.props.RefreshTableState){
							this.props.RefreshTableState(true)
						}
						this.setState({ loading: false, visible: false })
						// window.location.reload() //重新刷新页面
					}else{
						message.error(res.error || res.message);
						this.setState({ loading: false, visible: false})
						if(this.props.RefreshTableState){
							this.props.RefreshTableState(true)
						}
					}
				},
				formData:formData
			})
		}else{
			message.warning('没有选择分类')
			this.setState({ loading: false, visible: false })
		}
  }
  handleCancel=()=>{
		message.success('已取消')
    this.setState({ visible: false });
  }

	showModal=()=>{
		this.setState({
			visible:true,
			selectId:this.props.id
		})
    console.log(this.props.id,'选中的id');
		return false;
	}
	handleChannelPopId=(value)=>{
		this.setState({
			popId:value
		})
	}


  //字体颜色 ,color:"#108ee9"
  render() {
    const { visible, loading } = this.state
    return (
      <div>
      	<Button onClick={this.showModal}>{this.props.btnName}</Button>
        <Modal
          visible={visible}
          title={
						<p style={{fontSize:'18px'}}>选择分类
							<span style={{color:'#6b6b6b',fontSize:'12px',marginLeft:'8px'}}>(最多选择三个)</span>
						</p>}
          onOk={this.handleOk}
          onCancel={this.handleCancel}
          footer={[

            <Button key="back" size="large" onClick={this.handleCancel}>取消</Button>,
            <Button key="submit" type="primary" size="large" loading={loading} onClick={this.handleOk}>
              确定
            </Button>
          ]}
        >
          <PopTransfer  handleGetId={this.handleChannelPopId}/>
        </Modal>
      </div>
    )
  }

}

export default ModalTransfer
