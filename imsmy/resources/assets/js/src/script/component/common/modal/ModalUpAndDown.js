import React, { Component } from 'react'
import { Modal, Button, message } from 'antd'
const confirm = Modal.confirm
import Fetch from 'utils/fetch'
class ModalUpAndDown extends Component {
	constructor(props){
    super(props)
     this.state = {

  	}
  }

  showDeleteConfirm=()=>{
      confirm({
        title: `${this.props.btnName}排序`,
        content: `确定${this.props.btnName}吗？`,
        okText: '确定',
        cancelText: '取消',
        onOk() {
					let formData = new FormData()
					formData.append('id',this.props.id)
					Fetch.post({
						uri:this.props.uri,
						callback:(res)=>{
							if(res.message && res.message==='修改成功'){
								message.success(res.message)
								if(this.props.RefreshTableState){
									this.props.RefreshTableState(true)
								}
							}else{
								message.error(res.message || res.error)
								if(this.props.RefreshTableState){
									this.props.RefreshTableState(true)
								}
							}
						},
						formData:formData
					})
        },
        onCancel() {
          console.log('取消选择');
        }
      })
  }


  render() {
    return (
			<div>
       <Button onClick={this.showDeleteConfirm} >
       		{this.props.btnName}
      	</Button>
			</div>
    );
  }
}


export default ModalUpAndDown
