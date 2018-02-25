import React, { Component } from 'react'
import { Modal, Button, message } from 'antd'
const confirm = Modal.confirm
import Fetch from 'utils/fetch'
//解冻
class ModalThaw extends Component {
	constructor(props){
    super(props)
     this.state = {

  	}
		// console.log(this.props.goupUri,'kaishi');
  }

  showThaw=()=>{
      confirm({
        title: `${this.props.btnName}`,
        content: `确定将这${this.props.id.length!==undefined? this.props.id.length : ''}
									条数据${this.props.btnName}吗？`,
        okText: '确定',
        cancelText: '取消',
        onOk:()=>{
					if(this.props.thawUri !== ''){
						this.handleChangeThaw()
					}else{
						message.error('地址不能为空')
					}
        },
        onCancel:()=>{
          message.success('已取消');
        }
      })

  }
 handleChangeThaw=()=>{
	 let formData= new FormData()
	 if(this.props.id.length !==undefined){
		 const IdLength = this.props.id.length
		 if(IdLength ===1){
			 const newId = this.props.id.toString()
			 formData.append('id',newId)
		 }else if(IdLength >1){
			 const newId = this.props.id.join('|')
				formData.append('id',newId)
		 }
	 }else{
		 formData.append('id',this.props.id)
	 }
	 Fetch.post({
		 uri:this.props.thawUri,
		 callback:(res)=>{
			 // console.log(res,'dongjie');
			 if(res.message && res.message==='解冻成功'){
				 message.success(res.message)
				 if(this.props.RefreshTableState){
					 this.props.RefreshTableState(true)
				 }
			 }else{
				 console.log(res,'解冻错误');
				 message.error(res.message || res.error);
				 if(this.props.RefreshTableState){
					 this.props.RefreshTableState(true)
				 }
			 }
		 },
		 formData:formData
	 })
 }

  render() {
    return (
			<div>

       <Button onClick={this.showThaw} >
       		{this.props.btnName}
      	</Button>
			</div>
    );
  }

}


export default ModalThaw
